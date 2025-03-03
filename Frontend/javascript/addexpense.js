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
    // Automatically set the date to today
    let today = new Date().toISOString().split('T')[0]; 
    document.getElementById("date").value = today;
    document.getElementById("date").setAttribute("max", today); // Restrict future dates

    // Fetch and display the budget for the current month
    let currentMonth = new Date().toISOString().slice(0, 7);
    let budget = localStorage.getItem("budget_" + currentMonth);
    
    if (budget) {
        document.getElementById("budgetAmount").innerText = budget;
        document.getElementById("budgetBox").style.display = "block";
    }
    
    // Reset form fields on page refresh
    document.getElementById("expenseForm")?.reset();

    // Hide the "Other" payment method field
    document.getElementById("otherPaymentDiv").style.display = "none";
});