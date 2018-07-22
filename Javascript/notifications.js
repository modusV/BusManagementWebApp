

/* To use notifications: simply include:

     These lines inside <head>:

        <link rel="stylesheet" type="text/css" href="Layouts/Notifications.css">
        <script src="Javascript/notifications.js"></script>

     This whenever a notification is needed:

        <?php notify("success", Text::get(...)); ?>
        <?php notify("error", Text::get(...)); ?>
        <?php notify("other", Text::get(...)); ?>

*/

window.onload = function myFunction() {
    var x = document.getElementById("snackbar");
    x.className = "show";
    setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
}

