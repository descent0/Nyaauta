// Main JavaScript file for common functionality

document.addEventListener("DOMContentLoaded", () => {
  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  var tooltipList = tooltipTriggerList.map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl))

  // Initialize loading screen
  setTimeout(() => {
    const loading = document.querySelector(".loading")
    if (loading) {
      loading.style.display = "none"
    }
  }, 1000)

  // Add fade-in animation to cards
  const cards = document.querySelectorAll(".card")
  cards.forEach((card, index) => {
    card.style.animationDelay = index * 0.1 + "s"
    card.classList.add("fade-in")
  })
})

// Utility functions
function showAlert(message, type = "info") {
  const alertDiv = document.createElement("div")
  alertDiv.className = `alert alert-${type} alert-dismissible fade show`
  alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `

  const container = document.querySelector(".container")
  if (container) {
    container.insertBefore(alertDiv, container.firstChild)

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
      alertDiv.remove()
    }, 5000)
  }
}

function formatDate(dateString) {
  const options = { year: "numeric", month: "long", day: "numeric" }
  return new Date(dateString).toLocaleDateString(undefined, options)
}

function formatTime(timeString) {
  const options = { hour: "2-digit", minute: "2-digit" }
  return new Date("2000-01-01T" + timeString).toLocaleTimeString(undefined, options)
}

// File upload preview
function previewImage(input, previewId) {
  if (input.files && input.files[0]) {
    const reader = new FileReader()
    reader.onload = (e) => {
      document.getElementById(previewId).src = e.target.result
    }
    reader.readAsDataURL(input.files[0])
  }
}

// Form validation
function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return re.test(email)
}

function validateForm(formId) {
  const form = document.getElementById(formId)
  const inputs = form.querySelectorAll("input[required], select[required], textarea[required]")
  let isValid = true

  inputs.forEach((input) => {
    if (!input.value.trim()) {
      input.classList.add("is-invalid")
      isValid = false
    } else {
      input.classList.remove("is-invalid")
      input.classList.add("is-valid")
    }

    // Special validation for email
    if (input.type === "email" && input.value && !validateEmail(input.value)) {
      input.classList.add("is-invalid")
      isValid = false
    }
  })

  return isValid
}

// AJAX helper
function makeRequest(url, method = "GET", data = null) {
  return fetch(url, {
    method: method,
    headers: {
      "Content-Type": "application/json",
    },
    body: data ? JSON.stringify(data) : null,
  })
    .then((response) => response.json())
    .catch((error) => {
      console.error("Request failed:", error)
      throw error
    })
}
