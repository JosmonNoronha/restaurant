$(document).ready(function () {
    // Smooth scrolling for navigation links
    $("nav ul li a").on("click", function (event) {
        if (this.hash !== "") {
            event.preventDefault();
            var hash = this.hash;
            $("html, body").animate(
                {
                    scrollTop: $(hash).offset().top,
                },
                800
            );
        }
    });

    // Add active class to the current link
    $("nav ul li a").on("click", function () {
        $("nav ul li a").removeClass("active");
        $(this).addClass("active");
    });

    
});
