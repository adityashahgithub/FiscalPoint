document.addEventListener("DOMContentLoaded", function () {
    let today = new Date().toISOString().split('T')[0]; 
    let dateInput = document.getElementById("date");

    // Set today's date and max attribute for the date input
    if (dateInput) {
        dateInput.value = today;
        dateInput.setAttribute("max", today);
    }

    // Get the budget and expenses from the hidden fields
    let totalExpenses = parseFloat(document.getElementById("total_expenses").value) || 0;
    let budgetText = document.querySelector(".Budget p").innerText;
    let monthlyBudget = parseFloat(budgetText) || 0;

    // Calculate the initial remaining budget
    let remainingBudget = monthlyBudget - totalExpenses;

    // Display the remaining budget
    let remainingBudgetField = document.getElementById("remaining_budget");
    remainingBudgetField.value = remainingBudget.toFixed(2);

    // Handle input changes on the cost field
    let costInput = document.getElementById("cost");

    costInput.addEventListener("input", function () {
        let enteredAmount = parseFloat(this.value) || 0;  // Get entered amount (default to 0 if empty)
        
        // Calculate new remaining budget
        let newRemainingBudget = monthlyBudget - totalExpenses - enteredAmount;

        // Update the remaining budget field
        remainingBudgetField.value = newRemainingBudget.toFixed(2);

        // Show warning only if new remaining budget is negative
        if (newRemainingBudget < 0) {
            alert("Warning: This expense exceeds your remaining budget!");
            this.value = ""; // Clear the input field
            remainingBudgetField.value = (monthlyBudget - totalExpenses).toFixed(2); // Reset remaining budget
        }
    });

    // Ensure the "Other" payment method field is hidden by default
    document.getElementById("otherPaymentDiv").style.display = "none";

    // Attach validation to form submission
    let expenseForm = document.getElementById("expenseForm");
    if (expenseForm) {
        expenseForm.onsubmit = validateDate;
    }
});

function validateDate(event) {
    let selectedDate = document.getElementById("date").value;
    let today = new Date().toISOString().split('T')[0];

    if (selectedDate > today) {
        alert("You cannot add an expense for a future date!");
        event.preventDefault();
        return false;
    }
    return true;
}

function toggleOtherPayment() {
    let paymentMethod = document.getElementById("payment_method").value;
    let otherPaymentDiv = document.getElementById("otherPaymentDiv");

    otherPaymentDiv.style.display = (paymentMethod === "Other") ? "block" : "none";
}
