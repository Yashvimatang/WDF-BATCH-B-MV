// document
//   .getElementById("close-btn")
//   .addEventListener("click", function (event) {
//     let btn = document.getElementById("close-btn");
//     event.preventDefault();
//     window.location.href = "Pract1.html";
// });
// fetch("notifications.json").then(Response=>Response.json()).then(data=>
// {
//     let container = document.getElementById("Notificationbanner");
//     data.notifications.forEach(function(notifications){
//       let article = document.createElement("article");
//       article.classList.add("notification-item");
//       article.innerHTML = `
//        <h3>${notifications.Title}</h3>
//         <p>${notifications.Message}</p>
//        <small>${notifications.Date}</small>`;
//        container.appendChild(article);
//     });
// }
// )
// .catch(function(error){
//   console.log("Error in loading notifications:",error)
// });
// Handle the close button click and redirect
document.getElementById('close-btn').addEventListener('click', function (event) {
    event.preventDefault();
    window.location.href = 'Pract1.html';
});

// Fetch and display notifications
fetch('notifications.json')
    .then(response => response.json())
    .then(data => {
        let container = document.getElementById('Notificationbanner');
        
        data.notifications.forEach(function(notification) {
            let article = document.createElement('article');
            article.classList.add('notification-item');
            
            // Added correct backticks for template literals
            article.innerHTML = `
                <h3>${notification.Title}</h3>
                <p>${notification.Message}</p>
                <small>${notification.Date}</small>
            `;
            
            container.appendChild(article);
        });
    })
    .catch(function(error) {
        console.error('Error in loading notifications:', error);
    });
