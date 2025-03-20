// script.js

console.log(
  "Virginia Western Community College Cybersecurity Lab - Powered by Team 5 Solutions"
);

document.addEventListener("DOMContentLoaded", () => {
  console.log("DOM fully loaded and ready.");

  // ---------------------
  // Global Inactivity Timer (for login and logout pages)
  // ---------------------
  function startInactivityTimer() {
    let inactivityTimeout;
    function resetInactivityTimer() {
      clearTimeout(inactivityTimeout);
      inactivityTimeout = setTimeout(() => {
        window.location.href = "index.html";
      }, 300000);
    }
    document.addEventListener("mousemove", resetInactivityTimer);
    document.addEventListener("keydown", resetInactivityTimer);
    resetInactivityTimer();
  }

  // ======================
  // Login Page Functions
  // ======================
  if (document.body.id === "loginPage") {
    startInactivityTimer();

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
    document
      .getElementById("loginForm")
      .addEventListener("submit", handleLogin);
  }

  // ======================
  // Logout Page Functions (Single-Row Session)
  // ======================
  if (document.body.id === "logoutPage") {
    document
      .getElementById("logoutBtn")
      .addEventListener("click", function (e) {
        e.preventDefault();
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

        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
          if (this.readyState === 4) {
            if (this.status === 200) {
              const response = JSON.parse(this.responseText);
              if (!response.success) {
                document.getElementById("errorMsg").innerText =
                  response.message;
                return;
              }
              const sessions = response.sessions;
              if (!sessions || sessions.length === 0) {
                document.getElementById("errorMsg").innerText =
                  "No active sessions found for this name.";
                return;
              }
              const groups = {};
              sessions.forEach(function (session) {
                if (!groups[session.ClassNum]) {
                  groups[session.ClassNum] = [];
                }
                groups[session.ClassNum].push(session);
              });
              if (sessions.length === 1) {
                logoutSession(sessions[0].LoginNum, lastName);
              } else {
                const uniqueClasses = Object.keys(groups);
                if (uniqueClasses.length > 1) {
                  createClassDropdown(groups, lastName);
                } else {
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
      const formData = new FormData();
      formData.append("last_name", lastName);
      formData.append("loginNum", loginNum);
      xhr.open("POST", "../PHP/studentLogout.php", true);
      xhr.send(formData);
    }

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
        option.text = groups[classNum][0].ClassName;
        select.appendChild(option);
      }
      container.appendChild(select);
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
      btn.type = "button";
      btn.innerText = "Logout Session";
      btn.addEventListener("click", function () {
        const chosenSession = select.value;
        logoutSession(chosenSession, lastName);
      });
      container.appendChild(btn);
    }
  }

  // ======================
  // Admin Page Functions
  // ======================
  if (document.body.id === "adminPage") {
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
            window.location.href = "admin_dashboard.html";
          } else {
            document.getElementById("errorMsg").innerText =
              response.message || "Error logging in as admin.";
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

  // ======================
  // Admin Dashboard Page Functions
  // ======================
  if (document.body.id === "adminDashboardPage") {
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
          } else {
            document.getElementById("classMsg").innerText =
              "Error adding class.";
          }
        }
      };
      xhr.open("POST", "../PHP/adminDashboard.php?action=addClass", true);
      xhr.send(formData);
    }
    document
      .getElementById("addClassForm")
      .addEventListener("submit", addClass);

    function deleteClass(event) {
      event.preventDefault();
      const form = document.getElementById("deleteClassForm");
      const formData = new FormData(form);
      const xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            document.getElementById("classMsg").innerText = response.message;
          } else {
            document.getElementById("classMsg").innerText =
              "Error deleting class.";
          }
        }
      };
      xhr.open("POST", "../PHP/adminDashboard.php?action=deleteClass", true);
      xhr.send(formData);
    }
    document
      .getElementById("deleteClassForm")
      .addEventListener("submit", deleteClass);

    function downloadReport(event) {
      event.preventDefault();
      // Redirect the browser to download the CSV file.
      window.location.href = "../PHP/adminDashboard.php?action=downloadReport";
    }
    document
      .getElementById("downloadReportBtn")
      .addEventListener("click", downloadReport);

    // Admin Logout in Dashboard
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
  }
});
