document.addEventListener("DOMContentLoaded", function () {
    let today = new Date();
    let currentMonth = today.toISOString().slice(0, 7);
    document.getElementById("month").setAttribute("min", currentMonth);
});

function saveBudget(event) {
    event.preventDefault(); // Prevent form submission
    let month = document.getElementById("month").value;
    let budget = document.getElementById("budget").value;
    
    if (month && budget) {
        localStorage.setItem("budget_" + month, budget);
        alert("Budget set successfully for " + month);
        checkExistingBudget(); // Refresh reset button visibility
    }
}

function checkExistingBudget() {
    let month = document.getElementById("month").value;
    let storedBudget = localStorage.getItem("budget_" + month);

    if (storedBudget) {
        document.getElementById("budget").value = storedBudget;
        document.getElementById("resetBtn").style.display = "inline-block"; // Show Reset button
    } else {
        document.getElementById("budget").value = "";
        document.getElementById("resetBtn").style.display = "none"; // Hide Reset button
    }
}

function resetBudget() {
    let month = document.getElementById("month").value;

    if (confirm("Are you sure you want to reset the budget for " + month + "?")) {
        localStorage.removeItem("budget_" + month);
        document.getElementById("budget").value = "";
        document.getElementById("resetBtn").style.display = "none";
        alert("Budget reset successfully for " + month);
    }
}