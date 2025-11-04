<<<<<<< HEAD
<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    // Not logged in → redirect to login page
    header('Location: login.php');
    exit;
}
// If logged in, you can allow upload logic below
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        header {
    background-color: #0F0F0F;
    padding: 20px 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: fixed; /* fixa no topo */
    top: 0;
    left: 50%; /* centraliza horizontalmente */
    transform: translateX(-50%);
    width: 90%;
    max-width: 1200px;
    border-radius: 15px;
    box-shadow: 0 0 12px rgb(255, 255, 255);
    z-index: 9999;
}

.navbar-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: #edf0f1;
    font-weight: bold;
    font-size: 18px;
}

.navbar-brand img {
    width: 40px;
    height: 40px;
}

.nav_links {
    list-style: none;
    display: flex;
    align-items: center;
    gap: 20px;
    margin: 0;
    padding: 0;
}

.nav-item a {
    text-decoration: none;
    color: #edf0f1;
    font-weight: 500;
    transition: 0.3s;
}

.nav-item a:hover {
    color: #0088a2;
}

/* PROFILE */
.profile {
    display: flex;
    align-items: center;
    gap: 10px;
}

#logintext {
    color: #fff;
    font-weight: 500;
    padding: 6px 12px;
    border: 2px solid #fff;
    border-radius: 6px;
    transition: 0.3s;
}

#logintext:hover {
    transform: scale(1.08);
    box-shadow: 0 0 8px #fff;
}

.pfp {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 0 6px rgba(0, 136, 162, 0.4);
    cursor: pointer;
    transition: 0.3s;
}

.pfp:hover {
    transform: scale(1.05);
    box-shadow: 0 0 12px rgba(255, 255, 255, 0.822);
}

/* HAMBURGUER - RESPONSIVO */
.hamburguer {
    display: none;
    cursor: pointer;
    position: absolute;
    top: 25px;
    right: 40px;
    z-index: 1000;
}

.bar {
    display: block;
    width: 25px;
    height: 3px;
    margin: 5px;
    background-color: #edf0f1;
    transition: 0.3s;
}

@media (max-width: 768px) {
    .hamburguer { display: block; }

    .hamburguer.active .bar:nth-child(2) { opacity: 0; }
    .hamburguer.active .bar:nth-child(1) { transform: translateY(8px) rotate(45deg); }
    .hamburguer.active .bar:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }

    .nav_links {
        position: fixed;
        left: -100%;
        top: 70px;
        flex-direction: column;
        background-color: #0F0F0F;
        width: 100%;
        text-align: center;
        transition: 0.3s;
        padding: 20px 0;
    }

    .nav_links.active { left: 0; }
    .nav-item { margin: 16px 0; }
}
    </style>
</head>
<body>
    
</body>
=======
<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    // Not logged in → redirect to login page
    header('Location: login.php');
    exit;
}
// If logged in, you can allow upload logic below
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        header {
    background-color: #0F0F0F;
    padding: 20px 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: fixed; /* fixa no topo */
    top: 0;
    left: 50%; /* centraliza horizontalmente */
    transform: translateX(-50%);
    width: 90%;
    max-width: 1200px;
    border-radius: 15px;
    box-shadow: 0 0 12px rgb(255, 255, 255);
    z-index: 9999;
}

.navbar-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: #edf0f1;
    font-weight: bold;
    font-size: 18px;
}

.navbar-brand img {
    width: 40px;
    height: 40px;
}

.nav_links {
    list-style: none;
    display: flex;
    align-items: center;
    gap: 20px;
    margin: 0;
    padding: 0;
}

.nav-item a {
    text-decoration: none;
    color: #edf0f1;
    font-weight: 500;
    transition: 0.3s;
}

.nav-item a:hover {
    color: #0088a2;
}

/* PROFILE */
.profile {
    display: flex;
    align-items: center;
    gap: 10px;
}

#logintext {
    color: #fff;
    font-weight: 500;
    padding: 6px 12px;
    border: 2px solid #fff;
    border-radius: 6px;
    transition: 0.3s;
}

#logintext:hover {
    transform: scale(1.08);
    box-shadow: 0 0 8px #fff;
}

.pfp {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 0 6px rgba(0, 136, 162, 0.4);
    cursor: pointer;
    transition: 0.3s;
}

.pfp:hover {
    transform: scale(1.05);
    box-shadow: 0 0 12px rgba(255, 255, 255, 0.822);
}

/* HAMBURGUER - RESPONSIVO */
.hamburguer {
    display: none;
    cursor: pointer;
    position: absolute;
    top: 25px;
    right: 40px;
    z-index: 1000;
}

.bar {
    display: block;
    width: 25px;
    height: 3px;
    margin: 5px;
    background-color: #edf0f1;
    transition: 0.3s;
}

@media (max-width: 768px) {
    .hamburguer { display: block; }

    .hamburguer.active .bar:nth-child(2) { opacity: 0; }
    .hamburguer.active .bar:nth-child(1) { transform: translateY(8px) rotate(45deg); }
    .hamburguer.active .bar:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }

    .nav_links {
        position: fixed;
        left: -100%;
        top: 70px;
        flex-direction: column;
        background-color: #0F0F0F;
        width: 100%;
        text-align: center;
        transition: 0.3s;
        padding: 20px 0;
    }

    .nav_links.active { left: 0; }
    .nav-item { margin: 16px 0; }
}
    </style>
</head>
<body>
    
</body>
>>>>>>> e6ccc33e35b8ae902cdbae75d8f0dba6db4cc411
</html>