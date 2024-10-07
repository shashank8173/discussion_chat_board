// document.querySelector('.custom-upload').addEventListener('click', function() {
//     document.getElementById('fileInput').click();
// });

// document.getElementById('uploadForm').addEventListener('submit', function(event) {
//     var fileInput = document.getElementById('fileInput');
//     if (!fileInput.files.length) {
//         // Prevent form submission if no file is selected
//         event.preventDefault();
//         alert('Please select a file before submitting.');
//     }
// });

document.addEventListener("DOMContentLoaded", function () {
    const menuItems = document.querySelectorAll(".sidebar li");
    const contentPages = document.querySelectorAll(".content-page");
     console.log("Hello");
    menuItems.forEach(item => {
        item.addEventListener("click", function () {
            // Remove the active class from all menu items
            menuItems.forEach(i => i.classList.remove("active"));

            // Add active class to the clicked menu item
            this.classList.add("active");

            // Hide all content pages
            contentPages.forEach(page => page.classList.remove("active"));

            // Get the target content page from the clicked item's data-content attribute
            const targetContent = this.getAttribute("data-content");

            // Show the corresponding content page
            const targetPage = document.getElementById(targetContent);
            if (targetPage) {
                targetPage.classList.add("active");
            }
        });
    });
});



// ****************************itembsm*******************************
// Get all itembsm elements
const itembsmElements = document.querySelectorAll('.itembsm');
// Get all contentbsm elements
const contentbsmElements = document.querySelectorAll('.contentbsm');

// Function to handle the click and show/hide content
function showContent(event) {
    // Remove active class from all itembsm elements
    itembsmElements.forEach(itembsm => itembsm.classList.remove('active'));

    // Hide all contentbsm sections
    contentbsmElements.forEach(contentbsm => contentbsm.style.display = 'none');

    // Add active class to the clicked itembsm
    const clickedItembsm = event.currentTarget;
    clickedItembsm.classList.add('active');

    // Get the corresponding contentbsm based on the clicked itembsm's ID
    const contentbsmId = clickedItembsm.id.replace('itembsm', 'contentbsm');
    const contentbsmToShow = document.getElementById(contentbsmId);
    contentbsmToShow.style.display = 'block';
}

// Add click event listeners to each itembsm
itembsmElements.forEach(itembsm => {
    itembsm.addEventListener('click', showContent);
});

// **********************************dropdown status********************

// <!-- JavaScript for updating the status without reloading the page -->

function updateStatus(id, newStatus) {
    $.ajax({
        url: '../update_status.php',  // The PHP file to handle the request
        type: 'POST',
        data: {
            id: id,
            status: newStatus
        },
        success: function(response) {
            alert(response);  // Show a success message
            location.reload(); // Reload the page to reflect the changes
        },
        error: function() {
            alert("An error occurred while updating the status.");
        }
    });
}

// ******************move data to scheduled or delivered****************************
// function updateStatus(id, status) {
//     $.ajax({
//         url: 'your_php_file.php',  // Point to the PHP file handling the update
//         type: 'POST',
//         data: {
//             id: id,
//             status: status
//         },
//         success: function(response) {
//             alert(response); // Optional: For debugging purpose
//             location.reload();  // Reload page to reflect changes
//         },
//         error: function(xhr, status, error) {
//             console.error('Error:', error);
//         }
//     });
// }

// update carrent calendar  date

function updateAppointment(id, selectedDate) {
    $.ajax({
        url: 'update_appointment.php',  // The PHP file to handle the request
        type: 'POST',
        data: {
            id: id,
            appointment: selectedDate
        },
        success: function(response) {
            alert(response);  // Optionally show a success message
            // Dynamically update the appointment date in the DOM
            document.getElementById("appointment-" + id).innerText = selectedDate;  // Assuming you have an element with id `appointment-<id>` to display the date
        },
        error: function() {
            alert("An error occurred while updating the appointment.");
        }
    });
}

