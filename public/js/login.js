document.getElementById("loginForm").addEventListener("submit", function(e) {
    e.preventDefault();

    let email = document.getElementById("email").value;
    let password = document.getElementById("password").value;

    if(email === "" || password === "") {
        alert("Veuillez remplir tous les champs !");
        return;
    }

    // Simulation de connexion
    if(email === "admin@gmail.com" && password === "1234") {
        alert("Connexion réussie !");
        window.location.href = "dashboard.html";
    } else {
        alert("Email ou mot de passe incorrect !");
    }
});