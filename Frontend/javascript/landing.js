document.addEventListener("DOMContentLoaded", function () {
    const hiddenElements = document.querySelectorAll(".hidden");

    // Remove the "show" class to reset animations on refresh
    hiddenElements.forEach((el) => {
        el.classList.remove("show");
    });

    const observer = new IntersectionObserver(
        (entries, observer) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add("show");
                    }, index * 200); // Delay each element by 200ms
                    observer.unobserve(entry.target); // Ensures animation runs only once
                }
            });
        },
        { threshold: 0.2 } // Trigger when 20% of the section is visible
    );

    hiddenElements.forEach((el) => observer.observe(el));
});
