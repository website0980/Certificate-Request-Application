const apiUrl = 'api.php';

function showPage(pageId) {
  document.querySelectorAll('.page').forEach(page => page.classList.add('hidden'));
  const el = document.getElementById(pageId);
  if (el) el.classList.remove('hidden');
}

async function postApplication(application) {
  const response = await fetch(`${apiUrl}?action=save`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(application),
  });

  if (!response.ok) {
    const error = await response.json().catch(() => ({}));
    throw new Error(error.error || 'Unable to save application');
  }

  return response.json();
}

async function fetchSubmissions() {
  const response = await fetch(`${apiUrl}?action=list`);
  if (!response.ok) throw new Error('Unable to load submissions');
  return response.json();
}

async function fetchSubmission(id) {
  const response = await fetch(`${apiUrl}?action=get&id=${encodeURIComponent(id)}`);
  if (!response.ok) throw new Error('Unable to load submission');
  return response.json();
}

function formatDate(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString);
  if (Number.isNaN(date.getTime())) return dateString;
  return date.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
}

function setupIndexPage() {
  const startButton = document.getElementById('startButton');
  if (!startButton) return;

  startButton.addEventListener('click', () => showPage('form-page'));

  const form = document.getElementById('applicationForm');
  form.addEventListener('submit', async event => {
    event.preventDefault();
    const application = {
      name: form.name.value.trim(),
      designation: form.designation.value.trim(),
      office: form.office.value.trim(),
      purpose: form.purpose.value.trim(),
      date: form.date.value,
    };

    try {
      await postApplication(application);
      form.reset();
      showPage('success-page');
    } catch (error) {
      alert(error.message);
    }
  });

  document.getElementById('newApplicationButton').addEventListener('click', () => showPage('form-page'));
}

async function deleteSubmission(id) {
  const response = await fetch(`${apiUrl}?action=delete&id=${encodeURIComponent(id)}`, {
    method: 'POST',
  });

  if (!response.ok) {
    const error = await response.json().catch(() => ({}));
    throw new Error(error.error || 'Unable to delete submission');
  }

  return response.json();
}

async function setupAdminPage() {
  const countEl = document.getElementById('submissionCount');
  const listEl = document.getElementById('submissionList');
  if (!countEl || !listEl) return;

  async function refreshList() {
    try {
      const submissions = await fetchSubmissions();
      countEl.textContent = `Submitted forms: ${submissions.length}`;
      listEl.innerHTML = '';

      if (submissions.length === 0) {
        listEl.innerHTML = '<p>No submissions yet.</p>';
        return;
      }

      submissions.forEach(application => {
        const item = document.createElement('div');
        item.className = 'submission-item';
        item.innerHTML = `
          <h2>${application.name}</h2>
          <p><strong>Designation:</strong> ${application.designation}</p>
          <p><strong>Office / Agency:</strong> ${application.office}</p>
          <p><strong>Purpose:</strong> ${application.purpose}</p>
          <p><strong>Date:</strong> ${formatDate(application.date)}</p>
          <div class="button-row">
            <button type="button" class="primary-button" data-action="preview" data-id="${application.id}">Preview Fixed Format</button>
            <button type="button" class="secondary-button" data-action="delete" data-id="${application.id}">Delete</button>
          </div>
        `;
        listEl.appendChild(item);
      });
    } catch (error) {
      countEl.textContent = 'Error loading submissions';
      listEl.innerHTML = `<p>${error.message}</p>`;
    }
  }

  listEl.addEventListener('click', async event => {
    const button = event.target.closest('button');
    if (!button) return;
    const id = button.dataset.id;
    const action = button.dataset.action;
    if (!id || !action) return;

    if (action === 'preview') {
      window.open(`/preview.php?id=${encodeURIComponent(id)}`, '_blank');
      return;
    }

    if (action === 'delete') {
      if (!confirm('Delete this submission?')) return;
      try {
        await deleteSubmission(id);
        await refreshList();
      } catch (error) {
        alert(error.message);
      }
    }
  });

  await refreshList();
}

async function renderPreviewPage() {
  const previewContainer = document.getElementById('formattedPreview');
  const printButton = document.getElementById('printButton');
  if (!previewContainer || !printButton) return;

  const url = new URL(window.location.href);
  const id = url.searchParams.get('id');
  if (!id) {
    previewContainer.innerHTML = '<p>Submission ID is missing.</p>';
    return;
  }

  try {
    const application = await fetchSubmission(id);
    const copyHtml = [1, 2].map(() => `
      <section class="certificate-copy">
        <div class="certificate-bg-top"></div>
        <div class="certificate-bg-bottom"></div>
        <div class="certificate-content">
          <div class="certificate-top-bar">
            <span class="certificate-control">Control No:</span>
          </div>
          <div class="certificate-logo-row">
            <div class="certificate-logo logo-left"></div>
            <div class="certificate-logo logo-right"></div>
          </div>
          <div class="certificate-title">Certificate of Appearance</div>
          <p class="certificate-heading">To whom it may concern:</p>
          <p class="certificate-text">This is to certify that employee/officer whose name and designation stated hereunder appeared in this office as indicated and for the purpose stated.</p>

          <div class="certificate-form">
            <div class="certificate-field">
              <span class="field-label">Name:</span>
              <span class="field-value">${application.name}</span>
            </div>
            <div class="certificate-field">
              <span class="field-label">Designation:</span>
              <span class="field-value">${application.designation}</span>
            </div>
            <div class="certificate-field">
              <span class="field-label">Office/Agency:</span>
              <span class="field-value">${application.office}</span>
            </div>
            <div class="certificate-field">
              <span class="field-label">Purpose:</span>
              <span class="field-value">${application.purpose}</span>
            </div>
            <div class="certificate-field">
              <span class="field-label">Date:</span>
              <span class="field-value">${formatDate(application.date)}</span>
            </div>
          </div>

          <div class="certificate-signature">
            <div class="signature-name">Geraldine B. Abella</div>
            <div class="signature-title">City Tourism, Arts, Culture & Heritage Management Officer</div>
          </div>
        </div>
      </section>
    `).join('');

    previewContainer.innerHTML = copyHtml;
  } catch (error) {
    previewContainer.innerHTML = `<p>${error.message}</p>`;
  }

  printButton.addEventListener('click', () => window.print());
}

window.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('applicationForm')) {
    setupIndexPage();
  }
  if (document.getElementById('submissionCount')) {
    setupAdminPage();
  }
  if (document.getElementById('formattedPreview')) {
    renderPreviewPage();
  }
});
