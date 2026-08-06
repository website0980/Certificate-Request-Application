const apiUrl = 'api.php';

function showSection(id) {
  document.querySelectorAll('.app-section').forEach((section) => {
    section.classList.add('d-none');
  });
  const section = document.getElementById(id);
  if (section) {
    section.classList.remove('d-none');
  }
}

function showAlert(type, message) {
  const alertContainer = document.getElementById('formAlert');
  alertContainer.innerHTML = `\n    <div class="alert alert-${type} alert-dismissible fade show" role="alert">\n      ${message}\n      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>\n    </div>\n  `;
}

function validateApplicationForm(formData) {
  const requiredFields = ['full_name', 'designation', 'office', 'purpose', 'request_date'];
  const errors = [];
  requiredFields.forEach((field) => {
    if (!formData[field] || formData[field].trim() === '') {
      errors.push(field.replace('_', ' ') + ' is required.');
    }
  });
  return errors;
}

async function submitApplication() {
  const form = document.getElementById('applicationForm');
  const formData = {
    full_name: document.getElementById('fullName').value.trim(),
    designation: document.getElementById('designation').value.trim(),
    office: document.getElementById('office').value.trim(),
    purpose: document.getElementById('purpose').value.trim(),
    request_date: document.getElementById('requestDate').value,
    csrf_token: document.getElementById('csrfToken').value,
  };

  const errors = validateApplicationForm(formData);
  if (errors.length > 0) {
    showAlert('warning', `<strong>Validation failed:</strong><br>${errors.join('<br>')}`);
    return;
  }

  const submitButton = document.getElementById('submitApplicationButton');
  submitButton.disabled = true;
  submitButton.textContent = 'Submitting...';

  try {
    const response = await fetch(`${apiUrl}?action=save`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(formData),
    });

    const result = await response.json();
    if (!response.ok) {
      throw new Error(result.error || 'Unable to submit application.');
    }

    document.getElementById('referenceNumber').textContent = result.reference_no;
    showSection('successSection');
    form.reset();
  } catch (error) {
    showAlert('danger', error.message);
  } finally {
    submitButton.disabled = false;
    submitButton.textContent = 'Submit Application';
  }
}

window.addEventListener('DOMContentLoaded', () => {
  showSection('welcomeSection');

  document.getElementById('startButton').addEventListener('click', () => showSection('formSection'));
  document.getElementById('backToWelcome').addEventListener('click', () => showSection('welcomeSection'));
  document.getElementById('startButton').addEventListener('click', () => showSection('formSection'));
  document.getElementById('backToWelcome').addEventListener('click', () => showSection('welcomeSection'));
  document.getElementById('newApplicationButton').addEventListener('click', () => showSection('formSection'));
  document.getElementById('applicationForm').addEventListener('submit', (event) => {
    event.preventDefault();
    submitApplication();
  });
});
