function toggleOtherPayment() {
    var paymentMethod = document.getElementById("payment_method").value;
    var otherPaymentDiv = document.getElementById("otherPaymentDiv");

    if (paymentMethod === "Other") {
        otherPaymentDiv.style.display = "block";
    } else {
        otherPaymentDiv.style.display = "none";
    }
}

function validateDate(event) {
    let selectedDate = document.getElementById("date").value;
    let today = new Date().toISOString().split('T')[0]; // Get today's date (YYYY-MM-DD)

    if (selectedDate > today) {
        alert("You cannot add an expense for a future date!");
        event.preventDefault(); // Prevent form submission
        return false;
    }
    return true;
}

document.addEventListener("DOMContentLoaded", function () {
    let today = new Date().toISOString().split('T')[0]; 
    let dateInput = document.getElementById("date");

    // Set today's date and max attribute for the date input
    if (dateInput) {
        dateInput.value = today;
        dateInput.setAttribute("max", today);  // Ensure today's date can be selected
    }

    // Fetch and display the budget for the current month
    let currentMonth = new Date().toISOString().slice(0, 7); 
    let budget = parseFloat(document.getElementById("budgetAmount").innerText) || 0; // Use the value already fetched and displayed

    // Ensure budget exists and display it
    if (budget > 0) {
        document.getElementById("budgetBox").style.display = "block";  // Show the budget box
    } else {
        document.getElementById("budgetBox").style.display = "none";  // Hide budget box if no valid budget is found
    }

    // Reset form fields on page refresh
    document.getElementById("expenseForm")?.reset();

    // Hide the "Other" payment method field
    document.getElementById("otherPaymentDiv").style.display = "none";

    // Attach validation to form submission
    let expenseForm = document.getElementById("expenseForm");
    if (expenseForm) {
        expenseForm.onsubmit = validateDate;  // Ensure date validation before submission
    }

    // Update remaining budget live while typing
    let costInput = document.getElementById("cost");
    let remainingBudgetField = document.getElementById("remaining_budget");

    costInput.addEventListener("input", function () {
        let expenseAmount = parseFloat(this.value) || 0;  // Get entered amount (default to 0 if empty)
        let totalExpenses = parseFloat(document.getElementById("total_expenses").value) || 0;  // Fetch total expenses from the hidden field

        // Calculate remaining budget
        let newRemaining = budget - totalExpenses - expenseAmount;

        // If newRemaining is less than 0, show alert and reset input
        if (newRemaining < 0) {
            alert("Warning: This expense exceeds your remaining budget!");
            this.value = "";  // Clear the input field if the expense exceeds the budget
            newRemaining = budget - totalExpenses; // Reset to previous valid value
        }

        // Display the updated remaining budget
        remainingBudgetField.value = newRemaining.toFixed(2);
    });
});
