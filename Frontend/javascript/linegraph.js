document.addEventListener("DOMContentLoaded", function () {
    let ctx = document.getElementById("expenseChart").getContext("2d");
    let monthSelect = document.getElementById("monthSelect");

    let expenseChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: [],
            datasets: [{
                label: "Daily Expenses",
                data: [],
                borderColor: "rgba(75, 192, 192, 1)",
                backgroundColor: "rgba(75, 192, 192, 0.2)",
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: { display: true, text: "Day of the Month" }
                },
                y: {
                    title: { display: true, text: "Amount Spent (₹)" },
                    beginAtZero: true
                }
            }
        }
    });

    function fetchExpenseData(month) {
        fetch(`fetch_expense.php?month=${month}`)
            .then(response => response.json())
            .then(data => {
                let days = [];
                let expenses = [];

                data.forEach(entry => {
                    days.push(entry.day);
                    expenses.push(entry.total);
                });

                // Update Chart
                expenseChart.data.labels = days;
                expenseChart.data.datasets[0].data = expenses;
                expenseChart.update();
            })
            .catch(error => console.error("Error fetching data:", error));
    }

    // Load the current month’s data on page load
    fetchExpenseData(new Date().getMonth() + 1);

    // Update chart when user selects a month
    monthSelect.addEventListener("change", function () {
        fetchExpenseData(this.value);
    });
});
