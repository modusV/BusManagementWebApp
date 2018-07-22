

function checkCookie() {
    if(!navigator.cookieEnabled){
        window.location.replace('error.php');
    }
}
