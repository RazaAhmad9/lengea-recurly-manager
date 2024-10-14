var recurlyPublicKey = recurlyManagerCreateAccount.recurlyPublicKey;
recurly.configure(recurlyPublicKey); //  live
var recurlyElements = recurly.Elements();
var recurlyCardElement = recurlyElements.CardElement();
recurlyCardElement.attach("#recurly-elements");

var createAccountForm = document.querySelector("#createAccount");

createAccountForm.addEventListener("submit", function (event) {
    // Prevent the default form submission
    event.preventDefault();

    // Recurly token handling
    recurly.token(recurlyElements, createAccountForm, function (err, token) {
        if (err) {
            // Handle validation error
            displayError(err);
        } else {
            // Prepare form data
            var formData = new FormData();

            // Manually append all form fields, including the Recurly token
            var formElements = createAccountForm.elements;
            for (var i = 0; i < formElements.length; i++) {
                var field = formElements[i];
                if (field.name && field.value) {
                    formData.append(field.name, field.value);
                }
            }

            // Append the Recurly token
            formData.append('recurly-token', token.id);

            // Submit form data using fetch to the API endpoint
            fetch('/wp-json/recurly-manager/v1/process-subscription', {
                method: 'POST',
                body: formData,
            })
                .then(function (response) {
                    return response.json(); // Parse the JSON response
                })
                .then(function (response) {

                    console.log(response);
                    if (response.success) {
                        // Handle success (display a message or redirect)
                        showSuccessMessage(response.data); // Show a success message in the page
                    } else {
                        // Handle errors
                        displayError(response); // Display the error in the page
                    }
                })
                .catch(function (error) {

                    displayError(error); // Handle fetch errors
                });
        }
    });
});

// Function to display error messages in the page
function displayErrorRecurly(error) {
    resetMessages(); // Reset both success and error messages
    var errorElement = document.getElementById("recurly-errors");
    errorElement.innerHTML = ''; // Clear previous errors

    // Check if it's a Recurly error
    if (error.name === "validation" && error.details) {
        var errorMessage = error.message + "<br><strong>Errors in the following fields:</strong><ul>";

        // Loop through each field and its associated messages
        error.details.forEach(function (detail) {
            var field = detail.field.charAt(0).toUpperCase() + detail.field.slice(1); // Capitalize the field name
            var messages = detail.messages.join(', '); // Join multiple messages for the field
            errorMessage += "<li><strong>" + field + ":</strong> " + messages + "</li>";
        });

        errorMessage += "</ul>";
        errorElement.innerHTML = errorMessage;
    }
    // Check if it's a WP_Error object from the WordPress API
    else if (typeof error === "object" && error.errors) {
        var wpErrorMessage = "<strong>Errors occurred:</strong><ul>";

        // Loop through the WP_Error errors array
        for (var errorKey in error.errors) {
            if (error.errors.hasOwnProperty(errorKey)) {
                error.errors[errorKey].forEach(function (message) {
                    wpErrorMessage += "<li>" + message + "</li>";
                });
            }
        }

        wpErrorMessage += "</ul>";
        errorElement.innerHTML = wpErrorMessage;
    }
    // General case: handle unexpected error formats
    else if (typeof error === "object" && error.message) {
        // Display the error message directly
        errorElement.innerHTML = error.message;
    } else {
        errorElement.innerHTML = "An unexpected error occurred.";
    }

    // Show the error container
    errorElement.style.display = "block";
}

function displayError(response) {
    resetMessages(); // Reset both success and error messages
    var errorElement = document.getElementById("recurly-errors");
    errorElement.innerHTML = ''; // Clear previous errors

    // Check if the error contains a "data" object with a message (WP-style)
    if (response.data && response.data.message) {
        errorElement.innerHTML = response.data.message;
    }
    // Fallback to check for a general message
    else if (response.message) {
        errorElement.innerHTML = response.message;
    }
    // Handle unexpected error formats
    else {
        errorElement.innerHTML = "An unexpected error occurred.";
    }

    // Show the error container
    errorElement.style.display = "block";
}



// Function to display success messages in the page
function showSuccessMessage(data) {
    resetMessages(); // Reset both success and error messages
    var successElement = document.getElementById("recurly-success");
    successElement.innerHTML = data.message; // Show the success message
    successElement.style.display = "block";

    // Redirect to the success URL after 3 seconds
    if (data.redirect_to) {
        setTimeout(function () {
            window.location.href = data.redirect_to;
        }, 1000);
    }
}

// Function to reset both success and error messages
function resetMessages() {
    var errorElement = document.getElementById("recurly-errors");
    var successElement = document.getElementById("recurly-success");

    // Clear previous messages
    if (errorElement) {
        errorElement.innerHTML = '';
        errorElement.style.display = "none";
    }

    if (successElement) {
        successElement.innerHTML = '';
        successElement.style.display = "none";
    }
}





