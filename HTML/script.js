// script.js

console.log(
  "Virginia Western Community College Cybersecurity Lab - Powered by Team 5 Solutions"
);

// Global Inactivity Timer (used on login and logout pages)
function startInactivityTimer() {
  let inactivityTimeout;
  function resetInactivityTimer() {
    clearTimeout(inactivityTimeout);
    inactivityTimeout = setTimeout(() => {
      window.location.href = "index.html";
    }, 300000); // 5 minutes
  }
  document.addEventListener("mousemove", resetInactivityTimer);
  document.addEventListener("keydown", resetInactivityTimer);
  resetInactivityTimer();
}

// ----------------------
// Login Page Functions
// ----------------------
if (document.body.id === "loginPage") {
  startInactivityTimer();

  // Fetch available classes based on student name (using parameter "studentName")
  function fetchClasses() {
    const lastName = document.getElementById("last_name").value;
    if (lastName.trim() === "") {
      alert("Please enter a last name.");
      return;
    }
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (this.readyState === 4 && this.status === 200) {
        const response = JSON.parse(this.responseText);
        const classSelect = document.getElementById("class");
        classSelect.innerHTML = "";
        if (response.success) {
          response.classes.forEach(function (cls) {
            const option = document.createElement("option");
            // Assuming your SQL query aliases columns as classNum and className
            option.value = cls.classNum;
            option.text = cls.className;
            classSelect.appendChild(option);
          });
          document.getElementById("loginSection").style.display = "block";
          document.getElementById("errorMsg").innerText = "";
        } else {
          document.getElementById("errorMsg").innerText = response.message;
        }
      }
    };
    // Note the relative path to PHP folder and parameter name 'studentName'
    xhr.open(
      "GET",
      "../PHP/classSelection.php?studentName=" + encodeURIComponent(lastName),
      true
    );
    xhr.send();
  }
  document
    .getElementById("checkEnrollmentBtn")
    .addEventListener("click", fetchClasses);

  // Handle login form submission via POST to studentLogin.php
  function handleLogin(event) {
    event.preventDefault();
    const form = document.getElementById("loginForm");
    const formData = new FormData(form);
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (this.readyState === 4 && this.status === 200) {
        const response = JSON.parse(this.responseText);
        if (response.success) {
          document.getElementById("successMsg").innerText =
            "Login successful! Session ID: " + response.loginNum;
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
  document.getElementById("loginForm").addEventListener("submit", handleLogin);
}

// -----------------------
// Logout Page Functions (Updated for Single-Row Session)
// -----------------------
if (document.body.id === "logoutPage") {
  // Attach event handler to the "Logout" button (id="logoutBtn")
  document.getElementById("logoutBtn").addEventListener("click", function (e) {
    e.preventDefault();
    // Clear any previous messages and dropdown content
    document.getElementById("errorMsg").innerText = "";
    document.getElementById("successMsg").innerText = "";
    let container = document.getElementById("dropdownContainer");
    if (container) {
      container.innerHTML = "";
    }

    const lastName = document.getElementById("last_name").value.trim();
    if (lastName === "") {
      alert("Please enter a last name.");
      return;
    }

    // Retrieve active sessions via studentStatus.php using the new single-row design
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (this.readyState === 4) {
        if (this.status === 200) {
          const response = JSON.parse(this.responseText);
          if (!response.success) {
            document.getElementById("errorMsg").innerText = response.message;
            return;
          }
          const sessions = response.sessions;
          if (!sessions || sessions.length === 0) {
            document.getElementById("errorMsg").innerText =
              "No active sessions found for this name.";
            return;
          }

          // Group sessions by ClassNum
          const groups = {};
          sessions.forEach(function (session) {
            // Each session should include: LoginNum, ClassNum, and ClassName
            if (!groups[session.ClassNum]) {
              groups[session.ClassNum] = [];
            }
            groups[session.ClassNum].push(session);
          });

          // Determine flow based on how many sessions and classes:
          if (sessions.length === 1) {
            // Only one active session: log it out automatically.
            logoutSession(sessions[0].LoginNum, lastName);
          } else {
            const uniqueClasses = Object.keys(groups);
            if (uniqueClasses.length > 1) {
              // More than one class active: prompt the user to select a class.
              createClassDropdown(groups, lastName);
            } else {
              // Only one class, but multiple sessions: prompt the user to select a specific session.
              createSessionDropdown(groups[uniqueClasses[0]], lastName);
            }
          }
        } else {
          document.getElementById("errorMsg").innerText =
            "Error retrieving sessions.";
        }
      }
    };
    xhr.open(
      "GET",
      "../PHP/studentStatus.php?last_name=" + encodeURIComponent(lastName),
      true
    );
    xhr.send();
  });

  // Function to send the logout request (updates the active session row)
  function logoutSession(loginNum, lastName) {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (this.readyState === 4) {
        if (this.status === 200) {
          const resp = JSON.parse(this.responseText);
          if (resp.success) {
            // Display the success message in its dedicated element
            document.getElementById("successMsg").innerText = resp.message;
            document.getElementById("errorMsg").innerText = "";
            // Keep the success message visible for 5 seconds before clearing inputs and dropdowns
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
          document.getElementById("errorMsg").innerText = "Error logging out.";
        }
      }
    };
    const formData = new FormData();
    formData.append("last_name", lastName);
    formData.append("loginNum", loginNum);
    xhr.open("POST", "../PHP/studentLogout.php", true);
    xhr.send(formData);
  }

  // Create dropdown for class selection when multiple classes exist.
  function createClassDropdown(groups, lastName) {
    let container = document.getElementById("dropdownContainer");
    if (!container) {
      container = document.createElement("div");
      container.id = "dropdownContainer";
      document.body.appendChild(container);
    }
    container.innerHTML = "<p>Select a class:</p>";

    const select = document.createElement("select");
    select.id = "classSelect";
    for (let classNum in groups) {
      const option = document.createElement("option");
      option.value = classNum;
      option.text = groups[classNum][0].ClassName; // Use the ClassName from the first session in the group
      select.appendChild(option);
    }
    container.appendChild(select);

    const btn = document.createElement("button");
    btn.type = "button"; // Ensure button does not submit the form
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

  // Create dropdown for session selection when multiple sessions exist within a class.
  function createSessionDropdown(sessions, lastName) {
    let container = document.getElementById("dropdownContainer");
    if (!container) {
      container = document.createElement("div");
      container.id = "dropdownContainer";
      document.body.appendChild(container);
    }
    container.innerHTML = "<p>Select a session:</p>";

    const select = document.createElement("select");
    select.id = "sessionSelect";
    sessions.forEach(function (session) {
      const option = document.createElement("option");
      option.value = session.LoginNum;
      option.text =
        "Session " + session.LoginNum + " (" + session.ClassName + ")";
      select.appendChild(option);
    });
    container.appendChild(select);

    const btn = document.createElement("button");
    btn.type = "button"; // Ensure button does not trigger form submission
    btn.innerText = "Logout Session";
    btn.addEventListener("click", function () {
      const chosenSession = select.value;
      logoutSession(chosenSession, lastName);
    });
    container.appendChild(btn);
  }
}

// -----------------------
// Admin Page Functions
// -----------------------
if (document.body.id === "adminPage") {
  // Handle admin login form submission via POST to adminLogin.php
  function handleAdminLogin(event) {
    event.preventDefault();
    const form = document.getElementById("adminForm");
    const formData = new FormData(form);
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (this.readyState === 4 && this.status === 200) {
        const response = JSON.parse(this.responseText);
        if (response.success) {
          window.location.href = "admin_dashboard.html";
        } else {
          document.getElementById("errorMsg").innerText = response.message;
        }
      }
    };
    xhr.open("POST", "../PHP/adminLogin.php", true);
    xhr.send(formData);
  }
  document
    .getElementById("adminForm")
    .addEventListener("submit", handleAdminLogin);
}

// -------------------------------
// Admin Dashboard Page Functions
// -------------------------------
if (document.body.id === "adminDashboardPage") {
  // Add Student
  function addStudent(event) {
    event.preventDefault();
    const form = document.getElementById("addStudentForm");
    const formData = new FormData(form);
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        const response = JSON.parse(xhr.responseText);
        document.getElementById("studentMsg").innerText = response.message;
      }
    };
    xhr.open("POST", "../PHP/adminDashboard.php?action=addStudent", true);
    xhr.send(formData);
  }
  document
    .getElementById("addStudentForm")
    .addEventListener("submit", addStudent);

  // Delete Student
  function deleteStudent(event) {
    event.preventDefault();
    const form = document.getElementById("deleteStudentForm");
    const formData = new FormData(form);
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        const response = JSON.parse(xhr.responseText);
        document.getElementById("studentMsg").innerText = response.message;
      }
    };
    xhr.open("POST", "../PHP/adminDashboard.php?action=deleteStudent", true);
    xhr.send(formData);
  }
  document
    .getElementById("deleteStudentForm")
    .addEventListener("submit", deleteStudent);

  // Add Class
  function addClass(event) {
    event.preventDefault();
    const form = document.getElementById("addClassForm");
    const formData = new FormData(form);
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        const response = JSON.parse(xhr.responseText);
        document.getElementById("classMsg").innerText = response.message;
      }
    };
    xhr.open("POST", "../PHP/adminDashboard.php?action=addClass", true);
    xhr.send(formData);
  }
  document.getElementById("addClassForm").addEventListener("submit", addClass);

  // Delete Class
  function deleteClass(event) {
    event.preventDefault();
    const form = document.getElementById("deleteClassForm");
    const formData = new FormData(form);
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        const response = JSON.parse(xhr.responseText);
        document.getElementById("classMsg").innerText = response.message;
      }
    };
    xhr.open("POST", "../PHP/adminDashboard.php?action=deleteClass", true);
    xhr.send(formData);
  }
  document
    .getElementById("deleteClassForm")
    .addEventListener("submit", deleteClass);

  // Generate Report
  function generateReport(event) {
    event.preventDefault();
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4 && xhr.status === 200) {
        document.getElementById("reportSection").innerText = xhr.responseText;
      }
    };
    xhr.open("GET", "../PHP/adminDashboard.php?action=generateReport", true);
    xhr.send();
  }
  document
    .getElementById("generateReportBtn")
    .addEventListener("click", generateReport);
}
