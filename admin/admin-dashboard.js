// Handle Sidebar Navigation
function showDashboard() {
    document.getElementById("dashboard").style.display = "block";
    document.getElementById("manage-vehicles").style.display = "none";
}

function showManage() {
    document.getElementById("dashboard").style.display = "none";
    document.getElementById("manage-vehicles").style.display = "block";
    fetchJeepneys();
}

// Fetch Users Data from Server and Populate the Table
function fetchUsers() {
    fetch('/api/users')
        .then(response => response.json())
        .then(users => {
            const tableBody = document.querySelector("#userTable tbody");
            tableBody.innerHTML = ''; // Clear any existing rows

            users.forEach(user => {
                const row = document.createElement("tr");

                const firstNameCell = document.createElement("td");
                firstNameCell.textContent = user.firstName;
                row.appendChild(firstNameCell);

                const lastNameCell = document.createElement("td");
                lastNameCell.textContent = user.lastName;
                row.appendChild(lastNameCell);

                const contactNumberCell = document.createElement("td");
                contactNumberCell.textContent = user.contactNumber;
                row.appendChild(contactNumberCell);

                const emailCell = document.createElement("td");
                emailCell.textContent = user.email;
                row.appendChild(emailCell);

                const occupationCell = document.createElement("td");
                occupationCell.textContent = user.occupation;
                row.appendChild(occupationCell);

                // Add Action buttons (Ban, Unban, Delete)
                const actionsCell = document.createElement("td");

                // Check if the user is banned or not
                const banButton = document.createElement("button");
                banButton.classList.add("ban-btn");

                // If user is banned, show "Unban", otherwise show "Ban"
                if (user.isBanned) {
                    banButton.textContent = 'Unban';
                    banButton.onclick = () => unbanUser(user.userID);  // Unban the user
                } else {
                    banButton.textContent = 'Ban';
                    banButton.onclick = () => banUser(user.userID);  // Ban the user
                }

                actionsCell.appendChild(banButton);

                // Add delete button
                const deleteButton = document.createElement("button");
                deleteButton.textContent = 'Delete';
                deleteButton.onclick = () => deleteUser(user.userID); // Delete user
                actionsCell.appendChild(deleteButton);

                row.appendChild(actionsCell);

                tableBody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error fetching users:', error);
            alert('Error loading users data');
        });
}

// Ban User Function
function banUser(userID) {
    fetch(`/api/ban-user/${userID}`, {
        method: 'POST',
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User banned successfully!');
            fetchUsers(); // Refresh the user list
        } else {
            alert('Error banning user: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error banning user:', error);
        alert('An error occurred while banning the user.');
    });
}

// Unban User Function
function unbanUser(userID) {
    fetch(`/api/unban-user/${userID}`, {
        method: 'POST',
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User unbanned successfully!');
            fetchUsers(); // Refresh the user list
        } else {
            alert('Error unbanning user: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error unbanning user:', error);
        alert('An error occurred while unbanning the user.');
    });
}

// Delete User Function
function deleteUser(userID) {
    const confirmDelete = confirm("Are you sure you want to delete this user?");
    if (confirmDelete) {
        fetch(`/api/delete-user/${userID}`, {
            method: 'DELETE',
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User deleted successfully!');
                fetchUsers(); // Refresh the user list
            } else {
                alert('Error deleting user: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting user:', error);
            alert('An error occurred while deleting the user.');
        });
    }
}

// Sorting Function (ascending/descending)
function sortUsers(order) {
    const table = document.getElementById("userTable");
    const tbody = table.querySelector("tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));

    rows.sort((a, b) => {
        const aName = a.cells[0].textContent.toLowerCase();
        const bName = b.cells[0].textContent.toLowerCase();
        return (order === 'asc') ? aName.localeCompare(bName) : bName.localeCompare(aName);
    });

    tbody.innerHTML = "";
    rows.forEach(row => tbody.appendChild(row));
}

// Filter Users Function
function filterUsers() {
    const filterValue = document.getElementById("filter").value;
    const nameFilterValue = document.getElementById("nameFilter").value.toLowerCase();
    const table = document.getElementById("userTable");
    const rows = table.getElementsByTagName("tr");

    for (let i = 1; i < rows.length; i++) {
        const firstName = rows[i].cells[0].textContent.toLowerCase();
        const lastName = rows[i].cells[1].textContent.toLowerCase();
        const fullName = firstName + " " + lastName;
        const role = rows[i].cells[2].textContent.toLowerCase();

        rows[i].style.display = '';

        if (filterValue === 'name' && !fullName.includes(nameFilterValue)) {
            rows[i].style.display = 'none';
        } else if (filterValue === 'role' && role !== 'admin') {
            rows[i].style.display = 'none';
        } else if (filterValue === 'all') {
            rows[i].style.display = '';
        }
    }
}

// Logout function
function logout() {
    fetch('/logout', {
        method: 'POST',
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/';
        } else {
            alert('Error logging out');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error logging out');
    });
}

// Prevent back navigation on admin page
window.history.pushState(null, "", window.location.href);
window.onpopstate = function() {
    window.history.pushState(null, "", window.location.href);
};

// Fetch the users when the page loads
document.addEventListener('DOMContentLoaded', fetchUsers);

// Check if user is logged in and redirect if not
window.onload = function() {
    fetch('/api/check-login')
        .then(response => response.json())
        .then(data => {
            if (!data.loggedIn) {
                window.location.href = '/';
            }
        })
        .catch(error => {
            console.error('Error checking login status:', error);
            window.location.href = '/';
        });
};

// Add Manager Modal
function addManager() {
    document.getElementById("addManagerModal").style.display = "block";
}

// Close Manager Modal
function closeModal() {
    document.getElementById("addManagerModal").style.display = "none";
}

// Handle Form Submission for Add Manager
document.getElementById("addManagerForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const firstName = document.getElementById("firstName").value;
    const lastName = document.getElementById("lastName").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;

    fetch('/api/add-manager', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ firstName, lastName, email, password }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Manager added successfully!');
            closeModal();
            fetchUsers();
        } else {
            alert('Error adding manager: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the manager.');
    });
});

// Add Admin Modal
function addAdmin() {
    document.getElementById("addAdminModal").style.display = "block";
}

// Close Admin Modal
function closeAdminModal() {
    document.getElementById("addAdminModal").style.display = "none";
}

// Handle Form Submission for Add Admin
document.getElementById("addAdminForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const firstName = document.getElementById("adminFirstName").value;
    const lastName = document.getElementById("adminLastName").value;
    const email = document.getElementById("adminEmail").value;
    const password = document.getElementById("adminPassword").value;

    fetch('/api/add-admin', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ firstName, lastName, email, password }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Admin added successfully!');
            closeAdminModal();
        } else {
            alert('Error adding admin: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the admin.');
    });
});

/*Jeepneys*/
function fetchJeepneys() {
    fetch('/api/jeepneys')  
        .then(response => response.json())
        .then(jeepneys => {
            const tableBody = document.querySelector("#jeepneyTable tbody");
            tableBody.innerHTML = ''; 

            jeepneys.forEach(jeepney => {
                const row = document.createElement("tr");

                const jeepneyIDCell = document.createElement("td");
                jeepneyIDCell.textContent = jeepney.jeepneyID;  
                row.appendChild(jeepneyIDCell);

                const plateNumberCell = document.createElement("td");
                plateNumberCell.textContent = jeepney.plateNumber; 
                row.appendChild(plateNumberCell);

                const routeCell = document.createElement("td");
                routeCell.textContent = jeepney.route;  
                row.appendChild(routeCell);

                const typeCell = document.createElement("td");
                typeCell.textContent = jeepney.type; 
                row.appendChild(typeCell);

                const modifyButtonCell = document.createElement("td");
                const modifyButton = document.createElement("button");
                modifyButton.textContent = "Modify";
                modifyButton.classList.add("modify-btn");
                modifyButton.onclick = () => openJeepneyDetailsPopup(jeepney);  
                modifyButtonCell.appendChild(modifyButton);
                row.appendChild(modifyButtonCell);

                tableBody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error fetching jeepneys:', error);
            alert('Error loading jeepneys data');
        });
    }

function openJeepneyDetailsPopup(jeepney) {
    // Populate the modal with jeepney details
    document.getElementById("popupJeepneyID").textContent = jeepney.jeepneyID;
    document.getElementById("popupDriverID").textContent = jeepney.driverID;
    document.getElementById("popupPlateNumber").textContent = jeepney.plateNumber;
    document.getElementById("popupCapacity").textContent = jeepney.capacity;
    document.getElementById("popupOccupied").textContent = jeepney.occupied;
    document.getElementById("popupRoute").textContent = jeepney.route;
    document.getElementById("popupType").textContent = jeepney.type;
    document.getElementById("popupDepartureTime").textContent = jeepney.departure_time;
    document.getElementById("popupStatus").textContent = jeepney.status;
    document.getElementById("popupJeepneyImage").src = `data:image/jpeg;base64,${jeepney.jeep_image}`;

    // Display the modal
    document.getElementById("jeepneyDetailsPopup").style.display = "block";
}

function closeJeepneyDetailsPopup() {
    // Hide the modal
    document.getElementById("jeepneyDetailsPopup").style.display = "none";
}

function deleteJeepney() {
    const jeepneyID = document.getElementById("popupJeepneyID").textContent;

    fetch(`/api/jeepney/${jeepneyID}`, {
        method: 'DELETE',
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Jeepney deleted successfully');
            closeJeepneyDetailsPopup();
            fetchJeepneys(); // Refresh the list
        } else {
            alert('Failed to delete jeepney');
        }
    })
    .catch(error => {
        console.error('Error deleting jeepney:', error);
        alert('Error deleting jeepney');
    });
}