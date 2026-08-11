<?php
/**
 * foyer_ajouter.php
 * Insertion d'un nouveau foyer — FoyerConnect
 * Utilise : prepare() + execute() positionnels
 */
require_once 'connexion_bd.php';

$errors  = [];
$success = false;
$data    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ——— 1. Récupération & nettoyage ———
    $data = [
        'nom'         => trim($_POST['nom']         ?? ''),
        'ville'       => trim($_POST['ville']       ?? ''),
        'adresse'     => trim($_POST['adresse']     ?? ''),
        'telephone'   => trim($_POST['telephone']   ?? ''),
        'email'       => trim($_POST['email']       ?? ''),
        'capacite'    => trim($_POST['capacite']    ?? ''),
        'prix_min'    => trim($_POST['prix_min']    ?? ''),
        'prix_max'    => trim($_POST['prix_max']    ?? ''),
        'description' => trim($_POST['description'] ?? ''),
    ];

    // Gestion photo uploadée
    $photoName = 'default-foyer.jpg';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt)) {
            $errors[] = 'Format de photo non autorisé (jpg, jpeg, png, webp uniquement).';
        } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'La photo ne doit pas dépasser 2 Mo.';
        } else {
            $photoName = uniqid('foyer_') . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], '../images/' . $photoName);
        }
    } 

    // ——— 2. Validation PHP côté serveur ———
    if (empty($data['nom']))       $errors[] = 'Le nom du foyer est obligatoire.';
    if (strlen($data['nom']) > 150) $errors[] = 'Le nom ne doit pas dépasser 150 caractères.';
    if (empty($data['ville']))     $errors[] = 'La ville est obligatoire.';
    if (empty($data['adresse']))   $errors[] = 'L\'adresse est obligatoire.';
    if (!preg_match('/^\+?[0-9\s\-]{8,20}$/', $data['telephone']))
        $errors[] = 'Numéro de téléphone invalide.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
        $errors[] = 'Adresse email invalide.';
    if (!ctype_digit($data['capacite']) || (int)$data['capacite'] <= 0)
        $errors[] = 'La capacité doit être un entier positif.';
    if (!is_numeric($data['prix_min']) || $data['prix_min'] < 0)
        $errors[] = 'Le prix minimum doit être un nombre positif.';
    if (!is_numeric($data['prix_max']) || $data['prix_max'] < 0)
        $errors[] = 'Le prix maximum doit être un nombre positif.';
    if (!empty($data['prix_min']) && !empty($data['prix_max']) && (float)$data['prix_min'] > (float)$data['prix_max'])
        $errors[] = 'Le prix minimum ne peut pas être supérieur au prix maximum.';

    // ——— 3. Insertion PDO (paramètres positionnels) ———
    if (empty($errors)) {
        $pdo  = getDB();
        $sql  = "INSERT INTO foyers (nom, ville, adresse, telephone, email, capacite, prix_min, prix_max, photo, description)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);              // prepare()
        $stmt->execute([                           // execute() positionnel
            $data['nom'],
            $data['ville'],
            $data['adresse'],
            $data['telephone'],
            $data['email'],
            (int)$data['capacite'],
            (float)$data['prix_min'],
            (float)$data['prix_max'],
            $photoName,
            $data['description'],
        ]);
        $success = true;
        $data    = [];   // Réinitialiser le formulaire
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Foyer — FoyerConnect</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-card { background:#fff; padding:2.5rem; border-radius:16px; box-shadow:0 2px 12px rgba(0,0,0,.08); max-width:780px; margin:2rem auto; }
        .form-group { margin-bottom:1.4rem; }
        .form-group label { font-weight:600; font-size:.9rem; display:block; margin-bottom:.5rem; }
        .form-group input, .form-group select, .form-group textarea {
            width:100%; padding:.85rem 1.2rem; border:1.5px solid #dee2e6; border-radius:30px;
            font-size:.95rem; font-family:'Poppins',sans-serif; transition:all .2s; }
        .form-group textarea { border-radius:12px; min-height:100px; resize:vertical; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline:none; border-color:#0066cc; box-shadow:0 0 0 3px rgba(0,102,204,.12); }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .btn-submit { background:linear-gradient(135deg,#0066cc,#0052a3); color:#fff; border:none; padding:1rem 2rem;
            border-radius:30px; font-size:1rem; font-weight:700; cursor:pointer; width:100%; margin-top:.5rem; }
        .error-box { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; padding:1rem 1.5rem; border-radius:10px; margin-bottom:1.5rem; }
        .success-box { background:#d1fae5; border:1px solid #6ee7b7; color:#065f46; padding:1.5rem; border-radius:10px; text-align:center; margin-bottom:1.5rem; }
        .section-title { font-size:.78rem; text-transform:uppercase; letter-spacing:1.5px; color:#0066cc; font-weight:700;
            border-bottom:2px solid #e9ecef; padding-bottom:.5rem; margin-bottom:1.2rem; margin-top:1.5rem; }
        .required { color:#ef476f; margin-left:2px; }
    </style>
</head>
<body>
<?php include 'header_partial.php'; ?>
<main>
<div class="container">
    <h1><i class="fas fa-plus-circle"></i> Ajouter un Foyer</h1>

    <?php if ($success): ?>
        <div class="success-box">
            <i class="fas fa-check-circle" style="font-size:2rem"></i>
            <h3>Foyer ajouté avec succès !</h3>
            <p><a href="foyers_liste.php">← Retour à la liste</a> | <a href="foyer_ajouter.php">Ajouter un autre foyer</a></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <strong>Veuillez corriger les erreurs suivantes :</strong>
            <ul style="margin:.5rem 0 0 1rem">
                <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" enctype="multipart/form-data">
            <div class="section-title"><i class="fas fa-info-circle"></i> Informations générales</div>
            <div class="form-group">
                <label>Nom du foyer <span class="required">*</span></label>
                <input type="text" name="nom" value="<?= htmlspecialchars($data['nom'] ?? '') ?>" placeholder="ex: Foyer Universitaire El Amal" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Ville <span class="required">*</span></label>
                    <select name="ville" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach(['Tunis','Ariana','Ben Arous','Sfax','Sousse','Monastir','Bizerte','Nabeul','Gafsa','Sidi Bouzid'] as $v): ?>
                            <option value="<?= $v ?>" <?= (($data['ville'] ?? '') === $v)?'selected':'' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Capacité (places) <span class="required">*</span></label>
                    <input type="number" name="capacite" value="<?= htmlspecialchars($data['capacite'] ?? '') ?>" placeholder="ex: 80" min="1" required>
                </div>
            </div>
            <div class="form-group">
                <label>Adresse complète <span class="required">*</span></label>
                <input type="text" name="adresse" value="<?= htmlspecialchars($data['adresse'] ?? '') ?>" placeholder="Numéro, rue, code postal, ville" required>
            </div>

            <div class="section-title"><i class="fas fa-phone"></i> Coordonnées</div>
            <div class="form-row">
                <div class="form-group">
                    <label>Téléphone <span class="required">*</span></label>
                    <input type="tel" name="telephone" value="<?= htmlspecialchars($data['telephone'] ?? '') ?>" placeholder="+216 71 000 000" required>
                </div>
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>" placeholder="foyer@exemple.tn" required>
                </div>
            </div>

            <div class="section-title"><i class="fas fa-tag"></i> Tarification</div>
            <div class="form-row">
                <div class="form-group">
                    <label>Prix minimum (DT/mois) <span class="required">*</span></label>
                    <input type="number" name="prix_min" value="<?= htmlspecialchars($data['prix_min'] ?? '') ?>" placeholder="ex: 200" min="0" step="10" required>
                </div>
                <div class="form-group">
                    <label>Prix maximum (DT/mois) <span class="required">*</span></label>
                    <input type="number" name="prix_max" value="<?= htmlspecialchars($data['prix_max'] ?? '') ?>" placeholder="ex: 400" min="0" step="10" required>
                </div>
            </div>

            <div class="section-title"><i class="fas fa-image"></i> Photo & Description</div>
            <div class="form-group">
                <label>Photo du foyer <span style="color:#6c757d;font-weight:400">(facultatif — max 2 Mo, jpg/png/webp)</span></label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" style="border-radius:12px; padding:.6rem">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" placeholder="Décrivez le foyer, ses équipements, son emplacement..."><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Enregistrer le foyer</button>
        </form>
    </div>
</div>
</main>
<?php include 'footer_partial.php'; ?>
</body>
</html>