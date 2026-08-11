<?php
// Détecter depuis quel dossier on appelle le partial
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
/*if ($currentDir === 'php') {
    $root = '../';
    $htmlDir = '../html/';
    $cssDir = '../css/';
    $imgDir='../images/';
} else {
    $root = '';
    $htmlDir = 'html/';
    $cssDir = 'css/';
    $imgDir='images/';

}*/
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'FoyerConnect' ?></title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Styles communs aux pages PHP */
        .btn-action { display:inline-block; padding:.35rem .9rem; border-radius:30px; font-size:.82rem; font-weight:600; text-decoration:none; margin-right:.3rem; cursor:pointer; border:none; }
        .btn-edit   { background:#0066cc; color:#fff; }
        .btn-delete { background:#ef476f; color:#fff; }
        .btn-add    { background:linear-gradient(135deg,#ff8c42,#e6732e); color:#fff; padding:.7rem 1.5rem; border-radius:30px; font-weight:700; text-decoration:none; display:inline-block; }
        .btn-primary-php { background:linear-gradient(135deg,#0066cc,#0052a3); color:#fff; border:none; padding:.9rem 2rem; border-radius:30px; font-weight:700; cursor:pointer; font-size:1rem; }
        .table-responsive { overflow-x:auto; margin-top:1.5rem; }
        table { width:100%; border-collapse:collapse; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.07); }
        th { background:#0066cc; color:#fff; padding:1rem; text-align:left; font-weight:600; }
        td { padding:.85rem 1rem; border-bottom:1px solid #e9ecef; vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:#f0f6ff; }
        .form-card { background:#fff; padding:2.5rem; border-radius:16px; box-shadow:0 2px 12px rgba(0,0,0,.08); max-width:780px; margin:2rem auto; }
        .form-group { margin-bottom:1.4rem; }
        .form-group label { font-weight:600; font-size:.9rem; display:block; margin-bottom:.5rem; }
        .form-group input,.form-group select,.form-group textarea { width:100%; padding:.85rem 1.2rem; border:1.5px solid #dee2e6; border-radius:30px; font-size:.95rem; font-family:'Poppins',sans-serif; transition:all .2s; background:#fff; }
        .form-group textarea { border-radius:12px; min-height:100px; resize:vertical; }
        .form-group input:focus,.form-group select:focus,.form-group textarea:focus { outline:none; border-color:#0066cc; box-shadow:0 0 0 3px rgba(0,102,204,.12); }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .msg-error   { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; padding:1rem 1.5rem; border-radius:10px; margin-bottom:1.5rem; }
        .msg-success { background:#d1fae5; border:1px solid #6ee7b7; color:#065f46; padding:1rem 1.5rem; border-radius:10px; margin-bottom:1.5rem; }
        .section-title { font-size:.78rem; text-transform:uppercase; letter-spacing:1.5px; color:#0066cc; font-weight:700; border-bottom:2px solid #e9ecef; padding-bottom:.5rem; margin-bottom:1.2rem; margin-top:1.5rem; }
        .required { color:#ef476f; margin-left:2px; }
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem; }
        @media(max-width:768px){ .form-row{grid-template-columns:1fr} }
    </style>
</head>
<body>
<header>
    <nav>
        <div class="logo">
            <a href="../index.html">
                <img src="../images/logo 1.png" alt="FoyerConnect" style="height:40px;">
            </a>
        </div>
        <ul class="nav-links">
            <li><a href="../index.html">Accueil</a></li>
            <li><a href="../html/foyers.html">Foyers</a></li>
            <li><a href="../html/a-propos.html">À propos</a></li>
            <li><a href="../html/contact-updated.html">Contact</a></li>
            <li><a href="../html/espace-candidat-dynamique.html">Mon Espace</a></li>
            <li><a href="foyers_liste.php" style="color:#0066cc;font-weight:600">⚙ Admin</a></li>
            <li><a href="../html/connexion.html" class="btn-connexion">Connexion</a></li>
        </ul>
    </nav>
</header>
<main>