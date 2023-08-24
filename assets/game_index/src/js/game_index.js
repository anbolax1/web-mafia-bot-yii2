$( document ).ready(function() {
    let isRedirectToStarting = $("p#isRedirectToStating").text();
    if(isRedirectToStarting === 'true') {
        setTimeout(() => {  }, 5000);
        window.location.replace("starting");
    }
});
