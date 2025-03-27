// script.js

// Console banner message for dev tools
console.log(
  "Virginia Western Community College Cybersecurity Lab - Powered by Team 5 Solutions"
);

// =====================
// Global Inactivity Timer
// Logs out the user after 5 minutes of inactivity
// Used by login and logout pages to redirect to home
// =====================
function startInactivityTimer() {
  let inactivityTimeout;

  function resetInactivityTimer() {
    clearTimeout(inactivityTimeout);
    inactivityTimeout = setTimeout(() => {
      // Redirect user to the homepage after timeout
      window.location.href = "index.html";
    }, 300000); // 300,000ms = 5 minutes
  }

  // Listen for any user interaction
  document.addEventListener("mousemove", resetInactivityTimer);
  document.addEventListener("keydown", resetInactivityTimer);

  // Start the initial timer
  resetInactivityTimer();
}

// =====================
// Shared Utility: Toggle Password Visibility
// Used on admin login page
// =====================
function togglePasswordVisibility(event) {
  const passField = document.getElementById("admin_pass");

  if (passField) {
    // Toggle between 'text' and 'password' input types
    passField.type = event.target.checked ? "text" : "password";
  }
}

// =====================
// DOM Ready Handler
// Runs once the page has fully loaded
// =====================
document.addEventListener("DOMContentLoaded", () => {
  console.log("DOM fully loaded and ready.");

  // =====================
  // Login Page (login.html)
  // Handles student login process
  // =====================
  if (document.body.id === "loginPage") {
    // =====================
    // Trigger "Check Enrollment" on Enter key
    // If the class dropdown is still hidden, pressing Enter
    // acts the same as clicking the "Check Enrollment" button
    // =====================
    document
      .getElementById("loginForm")
      .addEventListener("keydown", function (event) {
        if (event.key === "Enter") {
          const lastNameField = document.getElementById("last_name");
          const classSelect = document.getElementById("class");

          if (
            document.getElementById("loginSection").style.display === "none"
          ) {
            event.preventDefault(); // Prevent form submission
            fetchClasses(); // Trigger class lookup
          }
        }
      });

    // Start auto-logout inactivity timer
    startInactivityTimer();

    // =====================
    // Toggle visibility of last name field
    // Default: hidden (like password). Checkbox reveals it.
    // =====================
    const lastNameField = document.getElementById("last_name");
    const toggleLastNameCheckbox = document.getElementById(
      "showLastNameCheckbox"
    );

    if (toggleLastNameCheckbox && lastNameField) {
      toggleLastNameCheckbox.addEventListener("change", function () {
        lastNameField.type = this.checked ? "text" : "password";
      });

      lastNameField.type = "password"; // Initial input is masked
    }

    // =====================
    // Fetch classes for the entered last name
    // Triggered by Check Enrollment button or Enter key
    // =====================
    function fetchClasses() {
      const lastName = document.getElementById("last_name").value;

      // Show alert if input is empty
      if (lastName.trim() === "") {
        alert("Please enter a last name.");
        return;
      }

      // AJAX call to back-end to fetch classes for this student
      const xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
          const response = JSON.parse(this.responseText);
          const classSelect = document.getElementById("class");
          classSelect.innerHTML = ""; // Clear any old options

          if (response.success) {
            // Populate dropdown with classes returned from the server
            response.classes.forEach(function (cls) {
              const option = document.createElement("option");
              option.value = cls.classNum;
              option.text = cls.classNum;
              classSelect.appendChild(option);
            });

            // Reveal second part of the form (class + login button)
            document.getElementById("loginSection").style.display = "block";
            document.getElementById("errorMsg").innerText = "";
          } else {
            document.getElementById("errorMsg").innerText = response.message;
          }
        }
      };

      xhr.open(
        "GET",
        "../PHP/classSelection.php?studentName=" + encodeURIComponent(lastName),
        true
      );
      xhr.send();
    }

    // Attach fetchClasses to the Check Enrollment button
    document
      .getElementById("checkEnrollmentBtn")
      .addEventListener("click", fetchClasses);

    // =====================
    // Submit full login (after selecting class)
    // =====================
    function handleLogin(event) {
      event.preventDefault(); // Prevent default page reload
      const form = document.getElementById("loginForm");
      const formData = new FormData(form);

      const xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
          const response = JSON.parse(this.responseText);

          if (response.success) {
            document.getElementById("successMsg").innerText =
              "Login successful! Session ID: " + response.loginNum;

            // Reset UI after 5 seconds
            setTimeout(() => {
              form.reset();
              document.getElementById("successMsg").innerText = "";
              document.getElementById("loginSection").style.display = "none";
            }, 5000);
          } else {
            document.getElementById("errorMsg").innerText = response.message;
          }
        }
      };

      xhr.open("POST", "../PHP/studentLogin.php", true);
      xhr.send(formData);
    }

    // Attach login handler to form submission
    document
      .getElementById("loginForm")
      .addEventListener("submit", handleLogin);
  }

  // =====================
  // Logout Page (logout.html)
  // Handles student logout and session selection
  // =====================
  if (document.body.id === "logoutPage") {
    // =====================
    // Toggle visibility of last name field
    // Default: hidden (like password). Checkbox reveals it.
    // =====================
    const lastNameField = document.getElementById("last_name");
    const showLastNameCheckbox = document.getElementById(
      "showLastNameCheckbox"
    );

    if (showLastNameCheckbox && lastNameField) {
      lastNameField.type = "password"; // Default to masked
      showLastNameCheckbox.addEventListener("change", function () {
        lastNameField.type = this.checked ? "text" : "password";
      });
    }

    // =====================
    // Trigger logout logic on Enter key
    // =====================
    document
      .getElementById("logoutForm")
      .addEventListener("keydown", function (event) {
        if (event.key === "Enter") {
          event.preventDefault();
          document.getElementById("logoutBtn").click();
        }
      });

    // =====================
    // Trigger logout button logic
    // =====================
    document
      .getElementById("logoutBtn")
      .addEventListener("click", function (e) {
        e.preventDefault();

        // Clear previous messages
        document.getElementById("errorMsg").innerText = "";
        document.getElementById("successMsg").innerText = "";

        // Clear previously loaded dropdowns
        let container = document.getElementById("dropdownContainer");
        if (container) {
          container.innerHTML = "";
        }

        // Get last name input
        const lastName = document.getElementById("last_name").value.trim();

        if (lastName === "") {
          alert("Please enter a last name.");
          return;
        }

        // AJAX call to get active sessions for this student
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
          if (this.readyState === 4) {
            if (this.status === 200) {
              const response = JSON.parse(this.responseText);

              // Show error if backend says it failed
              if (!response.success) {
                document.getElementById("errorMsg").innerText =
                  response.message;
                return;
              }

              const sessions = response.sessions;

              // No active sessions found
              if (!sessions || sessions.length === 0) {
                document.getElementById("errorMsg").innerText =
                  "No active sessions found for this name.";
                return;
              }

              // Group sessions by class
              const groups = {};
              sessions.forEach(function (session) {
                if (!groups[session.ClassNum]) {
                  groups[session.ClassNum] = [];
                }
                groups[session.ClassNum].push(session);
              });

              // If only one session, log it out automatically
              if (sessions.length === 1) {
                logoutSession(sessions[0].LoginNum, lastName);
              } else {
                // Multiple sessions — show class or session dropdown
                const uniqueClasses = Object.keys(groups);
                if (uniqueClasses.length > 1) {
                  createClassDropdown(groups, lastName);
                } else {
                  createSessionDropdown(groups[uniqueClasses[0]], lastName);
                }
              }
            } else {
              // Server error
              document.getElementById("errorMsg").innerText =
                "Error retrieving sessions.";
            }
          }
        };

        // Request to fetch session data
        xhr.open(
          "GET",
          "../PHP/studentStatus.php?last_name=" + encodeURIComponent(lastName),
          true
        );
        xhr.send();
      });

    // =====================
    // Helper: Log out a specific session
    // =====================
    function logoutSession(loginNum, lastName) {
      const xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
        if (this.readyState === 4) {
          if (this.status === 200) {
            const resp = JSON.parse(this.responseText);

            if (resp.success) {
              document.getElementById("successMsg").innerText = resp.message;

              setTimeout(() => {
                document.getElementById("successMsg").innerText = "";
                document.getElementById("last_name").value = "";
                let container = document.getElementById("dropdownContainer");
                if (container) {
                  container.innerHTML = "";
                }
              }, 5000);
            } else {
              document.getElementById("errorMsg").innerText = resp.message;
            }
          } else {
            document.getElementById("errorMsg").innerText =
              "Error logging out.";
          }
        }
      };

      // Prepare POST data
      const formData = new FormData();
      formData.append("last_name", lastName);
      formData.append("loginNum", loginNum);

      // Submit logout request
      xhr.open("POST", "../PHP/studentLogout.php", true);
      xhr.send(formData);
    }

    // =====================
    // Helper: Class selector dropdown
    // =====================
    function createClassDropdown(groups, lastName) {
      let container = document.getElementById("dropdownContainer");

      // Create container if missing (fallback)
      if (!container) {
        container = document.createElement("div");
        container.id = "dropdownContainer";
        document.body.appendChild(container);
      }

      container.innerHTML = "<p>Select a class:</p>";

      // Build dropdown of class options
      const select = document.createElement("select");
      select.id = "classSelect";

      for (let classNum in groups) {
        const option = document.createElement("option");
        option.value = classNum;
        option.text = groups[classNum][0].ClassNum;
        select.appendChild(option);
      }

      container.appendChild(select);

      // Button to confirm class choice
      const btn = document.createElement("button");
      btn.type = "button";
      btn.innerText = "Select Class";
      btn.addEventListener("click", function () {
        const chosenClass = select.value;
        const sessionsForClass = groups[chosenClass];

        if (sessionsForClass.length === 1) {
          logoutSession(sessionsForClass[0].LoginNum, lastName);
        } else {
          createSessionDropdown(sessionsForClass, lastName);
        }
      });

      container.appendChild(btn);
    }

    // =====================
    // Helper: Session selector dropdown
    // =====================
    function createSessionDropdown(sessions, lastName) {
      let container = document.getElementById("dropdownContainer");

      if (!container) {
        container = document.createElement("div");
        container.id = "dropdownContainer";
        document.body.appendChild(container);
      }

      container.innerHTML = "<p>Select a session:</p>";

      // Build dropdown of session options
      const select = document.createElement("select");
      select.id = "sessionSelect";

      sessions.forEach(function (session) {
        const option = document.createElement("option");
        option.value = session.LoginNum;
        option.text =
          "Session " + session.LoginNum + " (" + session.ClassNum + ")";
        select.appendChild(option);
      });

      container.appendChild(select);

      // Button to confirm session choice
      const btn = document.createElement("button");
      btn.type = "button";
      btn.innerText = "Logout Session";
      btn.addEventListener("click", function () {
        const chosenSession = select.value;
        logoutSession(chosenSession, lastName);
      });

      container.appendChild(btn);
    }
  }

  // =====================
  // Admin Page (admin.html)
  // Handles admin login with password expiration logic
  // =====================
  if (document.body.id === "adminPage") {
    // =====================
    // Toggle visibility of admin password field
    // =====================
    const showPasswordCheckbox = document.getElementById(
      "showPasswordCheckbox"
    );

    if (showPasswordCheckbox) {
      showPasswordCheckbox.addEventListener("change", togglePasswordVisibility);
    }

    // =====================
    // Submit admin login form
    // =====================
    function handleAdminLogin(event) {
      event.preventDefault();

      const form = document.getElementById("adminForm");
      const formData = new FormData(form);

      const xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
        if (this.readyState === 4) {
          let response = {};

          try {
            response = JSON.parse(this.responseText);
          } catch (e) {
            response.message = "Error logging in as admin.";
          }

          if (this.status === 200 && response.success) {
            if (response.passwordExpiringSoon) {
              showPasswordChangeForm(false, response.daysLeft);
            } else {
              window.location.href = "admin_dashboard.html";
            }
          } else if (response.alreadyLoggedIn) {
            const errDiv = document.getElementById("errorMsg");
            errDiv.innerHTML = "";

            const messageSpan = document.createElement("span");
            messageSpan.textContent = response.message;
            errDiv.appendChild(messageSpan);
            errDiv.appendChild(document.createElement("br"));

            const forceBtn = document.createElement("button");
            forceBtn.type = "button";
            forceBtn.textContent = "Force Logout & Re-Login";
            forceBtn.addEventListener("click", forceLogoutAndLogin);
            errDiv.appendChild(forceBtn);

            const dashBtn = document.createElement("button");
            dashBtn.type = "button";
            dashBtn.textContent = "Proceed to Dashboard";
            dashBtn.addEventListener("click", () => {
              window.location.href = "admin_dashboard.html";
            });

            errDiv.appendChild(document.createTextNode(" "));
            errDiv.appendChild(dashBtn);
          } else if (response.passwordExpired) {
            showPasswordChangeForm(true);
          } else if (response.passwordExpiringSoon) {
            showPasswordChangeForm(false, response.daysLeft);
          } else {
            document.getElementById("errorMsg").innerText =
              response.message || "Error logging in as admin.";
          }
        }
      };

      xhr.open("POST", "../PHP/adminLogin.php", true);
      xhr.send(formData);
    }

    // =====================
    // Handle force logout request if another session exists
    // =====================
    function forceLogoutAndLogin() {
      const xhrLogout = new XMLHttpRequest();
      xhrLogout.onreadystatechange = function () {
        if (xhrLogout.readyState === 4 && xhrLogout.status === 200) {
          handleAdminLogin(new Event("submit"));
        } else if (xhrLogout.readyState === 4) {
          document.getElementById("errorMsg").innerText =
            "Error forcing logout.";
        }
      };

      xhrLogout.open("POST", "../PHP/adminLogout.php", true);
      xhrLogout.send();
    }

    // =====================
    // Dynamically display password reset UI
    // Called when password is expired or about to expire
    // =====================
    function showPasswordChangeForm(forceChange, daysLeft = null) {
      const loginForm = document.getElementById("adminForm");
      loginForm.style.display = "none";

      const resetForm = document.getElementById("passwordResetForm");
      resetForm.style.display = "flex";

      const warningMsg = document.getElementById("passwordResetWarning");
      const resetPasswordMsg = document.getElementById("resetPasswordMsg");

      if (forceChange) {
        warningMsg.innerText =
          "Your password has expired or must be changed on first login. Please update it now.";
      } else if (daysLeft !== null) {
        warningMsg.innerText = `\u26a0\ufe0f Your password will expire in ${daysLeft} day${
          daysLeft !== 1 ? "s" : ""
        }. Consider changing it now.`;

        const continueBtn = document.createElement("button");
        continueBtn.textContent = "Continue without changing password";
        continueBtn.classList.add("btn-margin-top");
        continueBtn.addEventListener("click", () => {
          window.location.href = "admin_dashboard.html";
        });

        resetForm.appendChild(continueBtn);
      } else {
        warningMsg.innerText = "";
      }

      const showNewCheckbox = document.getElementById(
        "showNewPasswordCheckbox"
      );
      const newPassField = document.getElementById("newPassword");
      const confirmPassField = document.getElementById("confirmPassword");

      if (showNewCheckbox && newPassField && confirmPassField) {
        showNewCheckbox.addEventListener("change", function () {
          const type = this.checked ? "text" : "password";
          newPassField.type = type;
          confirmPassField.type = type;
        });
      }

      const submitBtn = document.getElementById("resetPasswordBtn");
      if (submitBtn) {
        submitBtn.onclick = function (e) {
          e.preventDefault();

          const newPass = newPassField.value.trim();
          const confirmPass = confirmPassField.value.trim();

          if (!newPass || !confirmPass) {
            resetPasswordMsg.innerText = "Both fields are required.";
            return;
          }

          if (newPass !== confirmPass) {
            resetPasswordMsg.innerText = "Passwords do not match.";
            return;
          }

          const xhr = new XMLHttpRequest();
          const formData = new FormData();
          formData.append("newPassword", newPass);

          xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
              try {
                const resp = JSON.parse(xhr.responseText);
                if (resp.success) {
                  resetPasswordMsg.innerText =
                    "✅ Password updated successfully! Redirecting...";
                  setTimeout(() => {
                    window.location.href = "admin_dashboard.html";
                  }, 3000);
                } else {
                  resetPasswordMsg.innerText =
                    resp.message || "Failed to update password.";
                }
              } catch (e) {
                resetPasswordMsg.innerText = "Unexpected error occurred.";
              }
            }
          };

          xhr.open("POST", "../PHP/changeAdminPassword.php", true);
          xhr.send(formData);
        };
      }
    }

    document
      .getElementById("adminForm")
      .addEventListener("submit", handleAdminLogin);
  }

  // ======================
  // Admin Dashboard Page Functions (admin_dashboard.html)
  // ======================
  if (document.body.id === "adminDashboardPage") {
    // Reset all dashboard forms and dropdowns
    function resetDashboard() {
      // Reset form fields
      document.getElementById("addStudentForm").reset();
      document.getElementById("enrollStudentForm").reset();
      document.getElementById("unenrollStudentForm").reset();
      document.getElementById("deleteStudentForm").reset();
      document.getElementById("addClassForm").reset();
      document.getElementById("deleteClassForm").reset();

      // Clear user feedback messages
      document.getElementById("studentMsg").innerText = "";
      document.getElementById("classMsg").innerText = "";

      // Reload dropdown options
      loadAllClassesForAddStudent();
      loadAllClassesForDelete();
    }

    // Populate class list for Enroll Student form
    function loadAllClassesForAddStudent() {
      const xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
          const response = JSON.parse(this.responseText);
          const select = document.getElementById("enrollClassNum");
          select.innerHTML = ""; // Clear existing options

          if (response.success && response.classes) {
            response.classes.forEach(function (cls) {
              const option = document.createElement("option");
              option.value = cls.ClassNum;
              option.text = `${cls.ClassName} (${cls.ClassNum})`;
              select.appendChild(option);
            });
          }
        }
      };
      xhr.open("GET", "../PHP/getAllClasses.php", true);
      xhr.send();
    }

    // Populate class list for Delete Class form
    function loadAllClassesForDelete() {
      const xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
          const response = JSON.parse(this.responseText);
          const select = document.getElementById("classNumDel");
          select.innerHTML = ""; // Clear existing options

          if (
            response.success &&
            response.classes &&
            response.classes.length > 0
          ) {
            response.classes.forEach(function (cls) {
              const option = document.createElement("option");
              option.value = cls.ClassNum;
              option.text = `${cls.ClassName} (${cls.ClassNum})`;
              select.appendChild(option);
            });
          } else {
            // No available classes fallback
            select.innerHTML = "<option value=''>No classes available</option>";
          }
        }
      };
      xhr.open("GET", "../PHP/getAllClasses.php", true);
      xhr.send();
    }

    // Preload classes on page load (delete form only — enroll will be handled separately)
    loadAllClassesForDelete();

    // Add Class handler
    function addClass(event) {
      event.preventDefault();
      const form = document.getElementById("addClassForm");
      const formData = new FormData(form);

      const xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            document.getElementById("classMsg").innerText = response.message;

            // Reset dashboard after short delay
            setTimeout(() => {
              resetDashboard();
            }, 5000);
          } else {
            document.getElementById("classMsg").innerText =
              "Error adding class.";
          }
        }
      };
      xhr.open("POST", "../PHP/adminDashboard.php?action=addClass", true);
      xhr.send(formData);
    }

    // Bind form submit event to addClass
    document
      .getElementById("addClassForm")
      .addEventListener("submit", addClass);

    // Delete Class handler
    function deleteClass(event) {
      event.preventDefault();
      const form = document.getElementById("deleteClassForm");
      const formData = new FormData(form);

      const xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          try {
            const response = JSON.parse(xhr.responseText);
            document.getElementById("classMsg").innerText = response.message;
          } catch (e) {
            document.getElementById("classMsg").innerText =
              "Error parsing server response.";
          }

          // Reset dashboard after delay
          setTimeout(() => {
            resetDashboard();
          }, 5000);
        }
      };
      xhr.open("POST", "../PHP/adminDashboard.php?action=deleteClass", true);
      xhr.send(formData);
    }

    // Bind form submit event to deleteClass
    document
      .getElementById("deleteClassForm")
      .addEventListener("submit", deleteClass);

    // Load classes a student is currently enrolled in
    function loadEnrolledClassesForStudent(studentName) {
      const xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
          const response = JSON.parse(this.responseText);
          const select = document.getElementById("unenrollClassNum");

          if (
            response.success &&
            response.classes &&
            response.classes.length > 0
          ) {
            select.innerHTML = "";
            response.classes.forEach(function (cls) {
              const option = document.createElement("option");
              option.value = cls.ClassNum;
              option.text = `${cls.ClassName} (${cls.ClassNum})`;
              select.appendChild(option);
            });

            document.getElementById("studentMsg").innerText = "";
          } else {
            select.innerHTML =
              "<option value=''>No enrolled classes found</option>";
            document.getElementById("studentMsg").innerText =
              "Student is not enrolled in any class.";
          }
        }
      };

      xhr.open(
        "GET",
        "../PHP/getStudentEnrollments.php?studentName=" +
          encodeURIComponent(studentName),
        true
      );
      xhr.send();
    }

    // Load dropdowns immediately when the dashboard loads
    loadAllClassesForAddStudent();
    loadAllClassesForDelete();

    // Auto-fetch enrolled classes when "Unenroll Student" name field loses focus
    document
      .getElementById("unenrollStudentName")
      .addEventListener("blur", function () {
        const studentName = this.value.trim();
        if (studentName !== "") {
          loadEnrolledClassesForStudent(studentName);
        }
      });

    // Add a new student (does not enroll them in a class yet)
    function addStudent(event) {
      event.preventDefault();
      const form = document.getElementById("addStudentForm");
      const formData = new FormData(form);

      const xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            document.getElementById("studentMsg").innerText = response.message;

            // Reset dashboard UI after short delay
            setTimeout(() => {
              resetDashboard();
            }, 5000);
          } else {
            document.getElementById("studentMsg").innerText =
              "Error adding student.";
          }
        }
      };

      xhr.open("POST", "../PHP/adminDashboard.php?action=addStudent", true);
      xhr.send(formData);
    }

    document
      .getElementById("addStudentForm")
      .addEventListener("submit", addStudent);

    // Enroll an existing student into a selected class
    function enrollStudent(event) {
      event.preventDefault();
      const form = document.getElementById("enrollStudentForm");
      const formData = new FormData(form);

      const xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            document.getElementById("studentMsg").innerText = response.message;
            setTimeout(() => {
              resetDashboard();
            }, 5000);
          } else {
            document.getElementById("studentMsg").innerText =
              "Error enrolling student.";
          }
        }
      };

      xhr.open("POST", "../PHP/adminDashboard.php?action=enrollStudent", true);
      xhr.send(formData);
    }

    document
      .getElementById("enrollStudentForm")
      .addEventListener("submit", enrollStudent);

    // Unenroll a student from a specific class
    function unenrollStudent(event) {
      event.preventDefault();
      const form = document.getElementById("unenrollStudentForm");
      const formData = new FormData(form);

      const xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            document.getElementById("studentMsg").innerText = response.message;
            setTimeout(() => {
              resetDashboard();
            }, 5000);
          } else {
            document.getElementById("studentMsg").innerText =
              "Error unenrolling student.";
          }
        }
      };

      xhr.open(
        "POST",
        "../PHP/adminDashboard.php?action=unenrollStudent",
        true
      );
      xhr.send(formData);
    }

    document
      .getElementById("unenrollStudentForm")
      .addEventListener("submit", unenrollStudent);

    // Completely delete a student and their enrollments
    function deleteStudent(event) {
      event.preventDefault();
      const form = document.getElementById("deleteStudentForm");
      const formData = new FormData(form);

      const xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            document.getElementById("studentMsg").innerText = response.message;
            setTimeout(() => {
              resetDashboard();
            }, 5000);
          } else {
            document.getElementById("studentMsg").innerText =
              "Error deleting student.";
          }
        }
      };

      xhr.open("POST", "../PHP/adminDashboard.php?action=deleteStudent", true);
      xhr.send(formData);
    }

    document
      .getElementById("deleteStudentForm")
      .addEventListener("submit", deleteStudent);

    // Download full login session report as a CSV file
    function downloadReport(event) {
      event.preventDefault();
      window.location.href = "../PHP/adminDashboard.php?action=downloadReport";
    }

    document
      .getElementById("downloadReportBtn")
      .addEventListener("click", downloadReport);

    // Navigate to view login logs in a new page
    document
      .getElementById("viewLogsBtn")
      .addEventListener("click", function (e) {
        e.preventDefault();
        window.location.href = "viewLogs.html";
      });

    // Admin logout from dashboard
    function handleAdminLogout() {
      const xhr = new XMLHttpRequest();

      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          let resp = {};
          try {
            resp = JSON.parse(xhr.responseText);
          } catch (e) {
            resp.message = "Error logging out.";
          }

          if (xhr.status === 200 && resp.success) {
            // Show logout success and redirect to home
            document.getElementById("adminLogoutMsg").innerText = resp.message;
            setTimeout(() => {
              window.location.href = "index.html";
            }, 5000);
          } else {
            document.getElementById("adminLogoutMsg").innerText =
              resp.message || "Error logging out.";
          }
        }
      };

      xhr.open("POST", "../PHP/adminLogout.php", true);
      xhr.send();
    }

    document
      .getElementById("adminLogoutBtn")
      .addEventListener("click", handleAdminLogout);
  } // END if adminDashboardPage
}); // END DOMContentLoaded
