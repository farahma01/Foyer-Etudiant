<?php
/**
 * foyer_modifier.php
 * Mise à jour d'un foyer — FoyerConnect
 * Utilise : prepare() + execute() nommés
 */
require_once 'connexion_bd.php';

$pdo     = getDB();
$errors  = [];
$success = false;

// Récupérer l'ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
    ?? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: foyers_liste.php');
    exit;
}

// Récupérer le foyer actuel
$stmt  = $pdo->prepare("SELECT * FROM foyers WHERE id = :id");
$stmt->execute([':id' => $id]);
$foyer = $stmt->fetch();

if (!$foyer) {
    header('Location: foyers_liste.php?msg=notfound');
    exit;
}

// ——— Traitement du formulaire ———
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // ——— Validation PHP côté serveur ———
    if (empty($data['nom']))       $errors[] = 'Le nom est obligatoire.';
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
        $errors[] = 'Prix minimum invalide.';
    if (!is_numeric($data['prix_max']) || $data['prix_max'] < 0)
        $errors[] = 'Prix maximum invalide.';
    if ((float)$data['prix_min'] > (float)$data['prix_max'])
        $errors[] = 'Le prix minimum ne peut pas dépasser le prix maximum.';

    // Gestion photo
    $photoName = $foyer['photo'];  // Conserver l'ancienne par défaut
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
            $errors[] = 'Format de photo non autorisé.';
        } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'La photo ne doit pas dépasser 2 Mo.';
        } else {
            $photoName = uniqid('foyer_') . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], '../images/' . $photoName);
        }
    }

    // ——— Mise à jour PDO (paramètres nommés) ———
    if (empty($errors)) {
        $sql = "UPDATE foyers SET
                    nom         = :nom,
                    ville       = :ville,
                    adresse     = :adresse,
                    telephone   = :telephone,
                    email       = :email,
                    capacite    = :capacite,
                    prix_min    = :prix_min,
                    prix_max    = :prix_max,
                    photo       = :photo,
                    description = :description
                WHERE id = :id";

        $updateStmt = $pdo->prepare($sql);          // prepare()
        $updateStmt->execute([                       // execute() nommé
            ':nom'         => $data['nom'],
            ':ville'       => $data['ville'],
            ':adresse'     => $data['adresse'],
            ':telephone'   => $data['telephone'],
            ':email'       => $data['email'],
            ':capacite'    => (int)$data['capacite'],
            ':prix_min'    => (float)$data['prix_min'],
            ':prix_max'    => (float)$data['prix_max'],
            ':photo'       => $photoName,
            ':description' => $data['description'],
            ':id'          => $id,
        ]);
        $success = true;
        // Recharger le foyer mis à jour
        $stmt->execute([':id' => $id]);
        $foyer = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Foyer — FoyerConnect</title>
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
        .success-box { background:#d1fae5; border:1px solid #6ee7b7; color:#065f46; padding:1rem 1.5rem; border-radius:10px; margin-bottom:1.5rem; }
        .section-title { font-size:.78rem; text-transform:uppercase; letter-spacing:1.5px; color:#0066cc; font-weight:700;
            border-bottom:2px solid #e9ecef; padding-bottom:.5rem; margin-bottom:1.2rem; margin-top:1.5rem; }
        .required { color:#ef476f; margin-left:2px; }
        .current-photo { margin-top:.5rem; }
    </style>
</head>
<body>
<?php include 'header_partial.php'; ?>
<main>
<div class="container">
    <h1><i class="fas fa-edit"></i> Modifier le Foyer #<?= $id ?></h1>

    <?php if ($success): ?>
        <div class="success-box"><i class="fas fa-check-circle"></i> Foyer mis à jour avec succès !
            <a href="foyers_liste.php" style="color:#065f46;font-weight:700;margin-left:1rem">← Retour à la liste</a>
        </div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="error-box"><strong>Erreurs :</strong><ul style="margin:.5rem 0 0 1rem"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $id ?>">

            <div class="section-title"><i class="fas fa-info-circle"></i> Informations générales</div>
            <div class="form-group">
                <label>Nom du foyer <span class="required">*</span></label>
                <input type="text" name="nom" value="<?= htmlspecialchars($foyer['nom']) ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Ville <span class="required">*</span></label>
                    <select name="ville" required>
                        <?php foreach(['Tunis','Ariana','Ben Arous','Sfax','Sousse','Monastir','Bizerte','Nabeul','Gafsa','Sidi Bouzid'] as $v): ?>
                            <option value="<?= $v ?>" <?= $foyer['ville']===$v?'selected':'' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Capacité <span class="required">*</span></label>
                    <input type="number" name="capacite" value="<?= $foyer['capacite'] ?>" min="1" required>
                </div>
            </div>
            <div class="form-group">
                <label>Adresse <span class="required">*</span></label>
                <input type="text" name="adresse" value="<?= htmlspecialchars($foyer['adresse']) ?>" required>
            </div>

            <div class="section-title"><i class="fas fa-phone"></i> Coordonnées</div>
            <div class="form-row">
                <div class="form-group">
                    <label>Téléphone <span class="required">*</span></label>
                    <input type="tel" name="telephone" value="<?= htmlspecialchars($foyer['telephone']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" value="<?= htmlspecialchars($foyer['email']) ?>" required>
                </div>
            </div>

            <div class="section-title"><i class="fas fa-tag"></i> Tarification</div>
            <div class="form-row">
                <div class="form-group">
                    <label>Prix minimum (DT/mois) <span class="required">*</span></label>
                    <input type="number" name="prix_min" value="<?= $foyer['prix_min'] ?>" min="0" step="10" required>
                </div>
                <div class="form-group">
                    <label>Prix maximum (DT/mois) <span class="required">*</span></label>
                    <input type="number" name="prix_max" value="<?= $foyer['prix_max'] ?>" min="0" step="10" required>
                </div>
            </div>

            <div class="section-title"><i class="fas fa-image"></i> Photo & Description</div>
            <div class="form-group">
                <label>Nouvelle photo <span style="color:#6c757d;font-weight:400">(laisser vide pour conserver l'actuelle)</span></label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" style="border-radius:12px;padding:.6rem">
                <?php if ($foyer['photo'] && $foyer['photo'] !== 'default-foyer.jpg'): ?>
                    <div class="current-photo"><small style="color:#6c757d">Photo actuelle :</small>
                        <img src="../images/<?= htmlspecialchars($foyer['photo']) ?>" alt="Photo actuelle" style="height:60px;border-radius:6px;margin-top:.3rem">
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description"><?= htmlspecialchars($foyer['description']) ?></textarea>
            </div>

            <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Enregistrer les modifications</button>
        </form>
    </div>
</div>
</main>
<?php include 'footer_partial.php'; ?>
</body>
</html>