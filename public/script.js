document.addEventListener("DOMContentLoaded", () => {
  // Mobile menu toggle
  const mobileMenuBtn = document.querySelector(".mobile-menu-btn")
  const navMenu = document.querySelector("nav ul")

  if (mobileMenuBtn && navMenu) {
    mobileMenuBtn.addEventListener("click", () => {
      navMenu.classList.toggle("show")
    })
  }

  // Search functionality
  const searchInput = document.getElementById("searchInput")
  const searchBtn = document.querySelector(".search-btn")

  if (searchInput && searchBtn) {
    searchBtn.addEventListener("click", () => {
      performSearch(searchInput.value)
    })

    searchInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        performSearch(searchInput.value)
      }
    })
  }

  function performSearch(query) {
    if (query.trim()) {
      // Simulate search functionality
      console.log("Searching for:", query)
      // In a real application, this would make an API call
      alert(`Searching for: "${query}"\n\nThis would normally search through articles, authors, and keywords.`)
    }
  }

  // Smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      const href = this.getAttribute("href")

      if (href !== "#") {
        e.preventDefault()

        const targetElement = document.querySelector(href)
        if (targetElement) {
          const headerHeight = document.querySelector("header").offsetHeight
          window.scrollTo({
            top: targetElement.offsetTop - headerHeight - 20,
            behavior: "smooth",
          })

          // Close mobile menu if open
          if (navMenu && navMenu.classList.contains("show")) {
            navMenu.classList.remove("show")
          }
        }
      }
    })
  })

  // Publication filters
  const filterBtns = document.querySelectorAll(".filter-btn")
  const publicationCards = document.querySelectorAll(".publication-card")

  filterBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      const filter = btn.getAttribute("data-filter")

      // Update active button
      filterBtns.forEach((b) => b.classList.remove("active"))
      btn.classList.add("active")

      // Filter publications
      publicationCards.forEach((card) => {
        const category = card.getAttribute("data-category")
        if (filter === "all" || category === filter) {
          card.style.display = "block"
          card.classList.add("fade-in")
        } else {
          card.style.display = "none"
          card.classList.remove("fade-in")
        }
      })
    })
  })

  // Password toggle functionality
  window.togglePassword = (inputId) => {
    const input = document.getElementById(inputId)
    const toggle = input.nextElementSibling
    const icon = toggle.querySelector("i")

    if (input.type === "password") {
      input.type = "text"
      icon.classList.remove("fa-eye")
      icon.classList.add("fa-eye-slash")
    } else {
      input.type = "password"
      icon.classList.remove("fa-eye-slash")
      icon.classList.add("fa-eye")
    }
  }

  // Password strength checker
  const passwordInput = document.getElementById("password")
  if (passwordInput) {
    passwordInput.addEventListener("input", checkPasswordStrength)
  }

  function checkPasswordStrength(e) {
    const password = e.target.value
    const strengthBar = document.querySelector(".strength-fill")
    const strengthText = document.querySelector(".strength-text")

    let strength = 0
    let feedback = "Very weak"

    // Check password criteria
    if (password.length >= 8) strength += 20
    if (password.match(/[a-z]/)) strength += 20
    if (password.match(/[A-Z]/)) strength += 20
    if (password.match(/[0-9]/)) strength += 20
    if (password.match(/[^a-zA-Z0-9]/)) strength += 20

    // Update strength bar
    if (strengthBar) {
      strengthBar.style.width = strength + "%"
    }

    // Update feedback text
    if (strength >= 80) feedback = "Very strong"
    else if (strength >= 60) feedback = "Strong"
    else if (strength >= 40) feedback = "Medium"
    else if (strength >= 20) feedback = "Weak"

    if (strengthText) {
      strengthText.textContent = `Password strength: ${feedback}`
    }
  }

  // Form validation for signup
  const signupForm = document.getElementById("signupForm")
  if (signupForm) {
    signupForm.addEventListener("submit", (e) => {
      e.preventDefault()

      const firstName = document.getElementById("firstName").value
      const lastName = document.getElementById("lastName").value
      const email = document.getElementById("email").value
      const password = document.getElementById("password").value
      const confirmPassword = document.getElementById("confirmPassword").value
      const terms = document.getElementById("terms").checked

      // Validation
      if (!firstName || !lastName || !email || !password || !confirmPassword) {
        showNotification("Please fill in all required fields.", "error")
        return
      }

      if (password !== confirmPassword) {
        showNotification("Passwords do not match!", "error")
        return
      }

      if (password.length < 8) {
        showNotification("Password must be at least 8 characters long.", "error")
        return
      }

      if (!terms) {
        showNotification("Please agree to the terms and conditions.", "error")
        return
      }

      // Simulate successful registration
      showNotification("Account created successfully! Redirecting to login...", "success")
      setTimeout(() => {
        window.location.href = "login.html"
      }, 2000)
    })
  }

  // Form validation for login
  const loginForm = document.getElementById("loginForm")
  if (loginForm) {
    loginForm.addEventListener("submit", (e) => {
      e.preventDefault()

      const email = document.getElementById("loginEmail").value
      const password = document.getElementById("loginPassword").value

      if (!email || !password) {
        showNotification("Please enter both email and password.", "error")
        return
      }

      // Simulate login
      showNotification("Login successful! Redirecting to dashboard...", "success")
      setTimeout(() => {
        window.location.href = "index.html"
      }, 2000)
    })
  }

  // Notification system
  function showNotification(message, type = "info") {
    // Remove existing notifications
    const existingNotification = document.querySelector(".notification")
    if (existingNotification) {
      existingNotification.remove()
    }

    // Create notification element
    const notification = document.createElement("div")
    notification.className = `notification notification-${type}`
    notification.innerHTML = `
      <div class="notification-content">
        <i class="fas ${type === "success" ? "fa-check-circle" : type === "error" ? "fa-exclamation-circle" : "fa-info-circle"}"></i>
        <span>${message}</span>
        <button class="notification-close">
          <i class="fas fa-times"></i>
        </button>
      </div>
    `

    // Add styles
    notification.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      background: ${type === "success" ? "#10b981" : type === "error" ? "#ef4444" : "#3b82f6"};
      color: white;
      padding: 16px 20px;
      border-radius: 8px;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
      z-index: 10000;
      max-width: 400px;
      animation: slideInRight 0.3s ease-out;
    `

    // Add to document
    document.body.appendChild(notification)

    // Close button functionality
    const closeBtn = notification.querySelector(".notification-close")
    closeBtn.addEventListener("click", () => {
      notification.remove()
    })

    // Auto remove after 5 seconds
    setTimeout(() => {
      if (notification.parentNode) {
        notification.remove()
      }
    }, 5000)
  }

  // Scroll animations
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("fade-in")
      }
    })
  }, observerOptions)

  // Observe elements for animation
  document.querySelectorAll(".area-card, .publication-card, .metric-card, .feature-highlight").forEach((el) => {
    observer.observe(el)
  })

  // Hero scroll indicator
  const scrollIndicator = document.querySelector(".hero-scroll-indicator")
  if (scrollIndicator) {
    scrollIndicator.addEventListener("click", () => {
      const aboutSection = document.getElementById("about")
      if (aboutSection) {
        aboutSection.scrollIntoView({ behavior: "smooth" })
      }
    })
  }

  // Add active class to current nav item
  const currentLocation = window.location.pathname
  const navLinks = document.querySelectorAll("nav ul li a")

  navLinks.forEach((link) => {
    if (
      link.getAttribute("href") === currentLocation ||
      (currentLocation.includes(link.getAttribute("href")) && link.getAttribute("href") !== "#")
    ) {
      link.classList.add("active")
    }
  })

  // Social auth buttons (placeholder functionality)
  const socialBtns = document.querySelectorAll(".social-btn")
  socialBtns.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault()
      const provider = btn.classList.contains("google-btn") ? "Google" : "ORCID"
      showNotification(`${provider} authentication would be implemented here.`, "info")
    })
  })

  // Add responsive styles for mobile menu
  const style = document.createElement("style")
  style.textContent = `
    @keyframes slideInRight {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }

    .notification-content {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .notification-close {
      background: none;
      border: none;
      color: white;
      cursor: pointer;
      padding: 4px;
      border-radius: 4px;
      transition: background-color 0.2s;
    }

    .notification-close:hover {
      background-color: rgba(255, 255, 255, 0.2);
    }

    nav ul.show {
      display: flex;
      flex-direction: column;
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background-color: white;
      padding: 20px;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
      z-index: 1000;
      border-radius: 0 0 12px 12px;
    }
    
    nav ul.show li {
      margin: 8px 0;
    }
    
    nav ul.show .btn {
      margin-top: 12px;
    }
    
    .active {
      color: var(--primary-color) !important;
      font-weight: 600 !important;
    }

    .active::after {
      width: 100% !important;
    }

    @media (max-width: 768px) {
      .hero-stats {
        flex-direction: column;
        align-items: center;
        gap: 1.5rem;
      }

      .stat-item {
        min-width: 120px;
      }

      .publication-filters {
        justify-content: flex-start;
        overflow-x: auto;
        padding-bottom: 8px;
      }

      .filter-btn {
        white-space: nowrap;
        flex-shrink: 0;
      }
    }
  `
  document.head.appendChild(style)

  // Initialize any animations on page load
  setTimeout(() => {
    document.querySelectorAll(".hero-content, .about-text").forEach((el) => {
      el.classList.add("fade-in")
    })
  }, 100)

  // View All Publications functionality
  if (window.location.pathname.includes("view-all.html")) {
    const categoryFilter = document.getElementById("categoryFilter")
    const yearFilter = document.getElementById("yearFilter")
    const sortFilter = document.getElementById("sortFilter")
    const applyFiltersBtn = document.getElementById("applyFilters")
    const viewBtns = document.querySelectorAll(".view-btn")
    const publicationsGrid = document.getElementById("publicationsGrid")
    const resultsCount = document.getElementById("resultsCount")

    // Filter functionality
    if (applyFiltersBtn) {
      applyFiltersBtn.addEventListener("click", () => {
        const category = categoryFilter.value
        const year = yearFilter.value
        const sort = sortFilter.value

        // Filter publications
        const publications = document.querySelectorAll(".publication-card")
        let visibleCount = 0

        publications.forEach((pub) => {
          const pubCategory = pub.getAttribute("data-category")
          const pubYear = pub.getAttribute("data-year")

          let show = true

          if (category !== "all" && pubCategory !== category) {
            show = false
          }

          if (year !== "all" && pubYear !== year) {
            show = false
          }

          if (show) {
            pub.style.display = "block"
            visibleCount++
          } else {
            pub.style.display = "none"
          }
        })

        // Update results count
        if (resultsCount) {
          resultsCount.textContent = `Showing ${visibleCount} articles`
        }

        // Sort publications if needed
        if (sort !== "newest") {
          sortPublications(sort)
        }
      })
    }

    // View toggle functionality
    viewBtns.forEach((btn) => {
      btn.addEventListener("click", () => {
        viewBtns.forEach((b) => b.classList.remove("active"))
        btn.classList.add("active")

        const view = btn.getAttribute("data-view")
        if (view === "list") {
          publicationsGrid.classList.add("list-view")
        } else {
          publicationsGrid.classList.remove("list-view")
        }
      })
    })

    function sortPublications(sortBy) {
      const publications = Array.from(document.querySelectorAll(".publication-card"))
      const container = publicationsGrid

      publications.sort((a, b) => {
        switch (sortBy) {
          case "title":
            const titleA = a.querySelector("h3").textContent
            const titleB = b.querySelector("h3").textContent
            return titleA.localeCompare(titleB)
          case "views":
            const viewsA = Number.parseInt(a.getAttribute("data-views") || "0")
            const viewsB = Number.parseInt(b.getAttribute("data-views") || "0")
            return viewsB - viewsA
          case "oldest":
            const yearA = Number.parseInt(a.getAttribute("data-year") || "0")
            const yearB = Number.parseInt(b.getAttribute("data-year") || "0")
            return yearA - yearB
          default:
            return 0
        }
      })

      // Re-append sorted publications
      publications.forEach((pub) => {
        if (pub.style.display !== "none") {
          container.appendChild(pub)
        }
      })
    }
  }

  // Article Details functionality
  if (window.location.pathname.includes("article-details.html")) {
    const copyBtn = document.querySelector(".copy-citation")
    const shareButtons = document.querySelectorAll(".share-btn")
    const downloadButtons = document.querySelectorAll(".download-btn")

    // Copy citation functionality
    if (copyBtn) {
      copyBtn.addEventListener("click", () => {
        const citation = copyBtn.parentElement.querySelector("p").textContent
        navigator.clipboard.writeText(citation).then(() => {
          showNotification("Citation copied to clipboard!", "success")
        })
      })
    }

    // Share functionality
    shareButtons.forEach((btn) => {
      btn.addEventListener("click", () => {
        const platform = btn.classList.contains("facebook")
          ? "Facebook"
          : btn.classList.contains("twitter")
            ? "Twitter"
            : btn.classList.contains("linkedin")
              ? "LinkedIn"
              : "Email"

        showNotification(`Share to ${platform} functionality would be implemented here.`, "info")
      })
    })

    // Download functionality
    downloadButtons.forEach((btn) => {
      btn.addEventListener("click", () => {
        const action = btn.textContent.trim()
        showNotification(`${action} functionality would be implemented here.`, "info")
      })
    })
  }

  // Pagination functionality
  const paginationNumbers = document.querySelectorAll(".pagination-number")
  const paginationBtns = document.querySelectorAll(".pagination-btn")

  paginationNumbers.forEach((btn) => {
    btn.addEventListener("click", () => {
      paginationNumbers.forEach((b) => b.classList.remove("active"))
      btn.classList.add("active")

      // Simulate page change
      showNotification(`Loading page ${btn.textContent}...`, "info")
    })
  })

  paginationBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      if (!btn.disabled) {
        const direction = btn.textContent.includes("Next") ? "next" : "previous"
        showNotification(`Loading ${direction} page...`, "info")
      }
    })
  })
})
