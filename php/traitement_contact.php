<?php
/**
 * traitement_contact.php
 * Traitement du formulaire de contact — FoyerConnect
 * Validation PHP + Insertion PDO
 */
require_once 'connexion_bd.php';

$errors  = [];
$success = false;
$data    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nom'     => trim($_POST['nom']     ?? ''),
        'email'   => trim($_POST['email']   ?? ''),
        'sujet'   => trim($_POST['sujet']   ?? ''),
        'message' => trim($_POST['message'] ?? ''),
    ];

    // ——— Validation PHP ———
    if (empty($data['nom']) || strlen($data['nom']) < 2)
        $errors['nom'] = 'Votre nom est obligatoire (min. 2 caractères).';

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'Adresse email invalide.';

    if (empty($data['sujet']) || strlen($data['sujet']) < 5)
        $errors['sujet'] = 'Le sujet est obligatoire (min. 5 caractères).';
    if (strlen($data['sujet']) > 255)
        $errors['sujet'] = 'Le sujet ne doit pas dépasser 255 caractères.';

    if (empty($data['message']) || strlen($data['message']) < 20)
        $errors['message'] = 'Le message est obligatoire (min. 20 caractères).';
    if (strlen($data['message']) > 2000)
        $errors['message'] = 'Le message ne doit pas dépasser 2000 caractères.';

    if (empty($errors)) {
        $pdo  = getDB();
        $sql  = "INSERT INTO contacts (nom, email, sujet, message) VALUES (:nom, :email, :sujet, :message)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom'     => $data['nom'],
            ':email'   => $data['email'],
            ':sujet'   => $data['sujet'],
            ':message' => $data['message'],
        ]);
        $success = true;
        $data    = [];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact — FoyerConnect</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/contact.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .field-error { color:#ef476f; font-size:.82rem; margin-top:.3rem; padding-left:.5rem; display:block; }
        .error-field { border-color:#ef476f !important; }
        .success-box { background:#d1fae5; border:1px solid #6ee7b7; color:#065f46; padding:2rem; border-radius:16px; text-align:center; margin-bottom:2rem; }
        textarea.form-group-input { border-radius:12px; }
        .char-count { font-size:.8rem; color:#6c757d; text-align:right; margin-top:.2rem; }
    </style>
</head>
<body>
<?php include 'header_partial.php'; ?>
<main>
<div class="container">
    <h1><i class="fas fa-envelope"></i> Contactez-nous</h1>

    <?php if ($success): ?>
    <div class="success-box">
        <div style="font-size:3rem">✅</div>
        <h2>Message envoyé !</h2>
        <p>Nous vous répondrons dans les meilleurs délais à l'adresse <?= htmlspecialchars($_POST['email'] ?? '') ?></p>
        <a href="../index.html" style="display:inline-block;margin-top:1rem;background:#065f46;color:#fff;padding:.8rem 2rem;border-radius:30px;font-weight:700;text-decoration:none">← Retour à l'accueil</a>
    </div>
    <?php else: ?>

    <div class="contact-grid">
        <!-- Info -->
        <div class="contact-info">
            <h2>Nos coordonnées</h2>
            <p><i class="fas fa-map-marker-alt" style="color:#0066cc"></i> Tunisie, Tunis</p>
            <p><i class="fas fa-phone" style="color:#0066cc"></i> +216 71 658 857</p>
            <p><i class="fas fa-envelope" style="color:#0066cc"></i> contact@foyerconnect.tn</p>
            <p><i class="fas fa-clock" style="color:#0066cc"></i> Lun–Ven : 8h00 – 17h00</p>
        </div>

        <!-- Formulaire -->
        <div class="contact-form">
            <h2>Envoyer un message</h2>
            <form method="POST" id="contactForm" novalidate>
                <div class="form-group">
                    <label>Nom complet <span style="color:#ef476f">*</span></label>
                    <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($data['nom'] ?? '') ?>"
                           class="<?= isset($errors['nom'])?'error-field':'' ?>" placeholder="Votre nom">
                    <?php if(isset($errors['nom'])): ?><span class="field-error"><?= htmlspecialchars($errors['nom']) ?></span><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Email <span style="color:#ef476f">*</span></label>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>"
                           class="<?= isset($errors['email'])?'error-field':'' ?>" placeholder="votre@email.tn">
                    <?php if(isset($errors['email'])): ?><span class="field-error"><?= htmlspecialchars($errors['email']) ?></span><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Sujet <span style="color:#ef476f">*</span></label>
                    <input type="text" name="sujet" id="sujet" value="<?= htmlspecialchars($data['sujet'] ?? '') ?>"
                           class="<?= isset($errors['sujet'])?'error-field':'' ?>" placeholder="Objet de votre message" maxlength="255">
                    <?php if(isset($errors['sujet'])): ?><span class="field-error"><?= htmlspecialchars($errors['sujet']) ?></span><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Message <span style="color:#ef476f">*</span></label>
                    <textarea name="message" id="message" rows="6"
                              class="<?= isset($errors['message'])?'error-field':'' ?>"
                              style="border-radius:12px;resize:vertical"
                              placeholder="Votre message (min. 20 caractères)" maxlength="2000"><?= htmlspecialchars($data['message'] ?? '') ?></textarea>
                    <div class="char-count"><span id="charCount">0</span>/2000</div>
                    <?php if(isset($errors['message'])): ?><span class="field-error"><?= htmlspecialchars($errors['message']) ?></span><?php endif; ?>
                </div>
                <button type="submit" class="btn-primary" style="width:100%"><i class="fas fa-paper-plane"></i> Envoyer le message</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>
</main>
<?php include 'footer_partial.php'; ?>

<script>
// Compteur de caractères pour le message
const msgArea = document.getElementById('message');
const counter = document.getElementById('charCount');
if (msgArea && counter) {
    counter.textContent = msgArea.value.length;
    msgArea.addEventListener('input', () => counter.textContent = msgArea.value.length);
}
</script>
</body>
</html>