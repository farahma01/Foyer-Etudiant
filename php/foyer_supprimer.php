<?php
/**
 * foyer_supprimer.php
 * Suppression d'un foyer — FoyerConnect
 * Utilise : prepare() + execute() positionnel
 */
require_once 'connexion_bd.php';

$pdo     = getDB();
$message = '';
$msgType = '';
$foyer   = null;

// ——— Récupérer le foyer à supprimer ———
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    header('Location: foyers_liste.php');
    exit;
}

// Récupérer les infos du foyer (prepare + execute positionnel)
$stmt  = $pdo->prepare("SELECT * FROM foyers WHERE id = ?");
$stmt->execute([$id]);
$foyer = $stmt->fetch();

if (!$foyer) {
    header('Location: foyers_liste.php?msg=notfound');
    exit;
}

// ——— Traitement de la suppression ———
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer'])) {
    // Vérifier qu'aucune demande en attente n'est liée
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM demandes WHERE foyer_id = ? AND statut = 'en_attente'");
    $checkStmt->execute([$id]);
    $nbAttente = (int)$checkStmt->fetchColumn();

    if ($nbAttente > 0) {
        $message = "Impossible de supprimer ce foyer : $nbAttente demande(s) en attente lui sont associées.";
        $msgType = 'error';
    } else {
        // exec() pour les opérations simples sans paramètres utilisateur
        // Ici on utilise prepare + execute pour la sécurité
        $delStmt = $pdo->prepare("DELETE FROM foyers WHERE id = ?");
        $delStmt->execute([$id]);
        $message = "Le foyer « " . htmlspecialchars($foyer['nom']) . " » a été supprimé avec succès.";
        $msgType = 'success';
        $foyer   = null;   // Foyer supprimé
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer un Foyer — FoyerConnect</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .confirm-card { background:#fff; padding:2.5rem; border-radius:16px; box-shadow:0 2px 12px rgba(0,0,0,.08); max-width:600px; margin:2rem auto; text-align:center; }
        .foyer-info { background:#f8f9fa; padding:1.5rem; border-radius:12px; text-align:left; margin:1.5rem 0; }
        .foyer-info table { width:100%; border-collapse:collapse; }
        .foyer-info td { padding:.4rem .5rem; border-bottom:1px solid #e9ecef; }
        .foyer-info td:first-child { font-weight:600; color:#6c757d; width:35%; }
        .btn-row { display:flex; gap:1rem; justify-content:center; margin-top:1.5rem; flex-wrap:wrap; }
        .btn-delete { background:#ef476f; color:#fff; border:none; padding:.9rem 2rem; border-radius:30px; font-weight:700; cursor:pointer; font-size:1rem; }
        .btn-cancel { background:#e9ecef; color:#343a40; text-decoration:none; padding:.9rem 2rem; border-radius:30px; font-weight:700; font-size:1rem; }
        .msg-error   { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; padding:1rem 1.5rem; border-radius:10px; margin-bottom:1.5rem; }
        .msg-success { background:#d1fae5; border:1px solid #6ee7b7; color:#065f46; padding:1rem 1.5rem; border-radius:10px; margin-bottom:1.5rem; }
        .warn-icon { font-size:4rem; color:#ef476f; margin-bottom:1rem; }
    </style>
</head>
<body>
<?php include 'header_partial.php'; ?>
<main>
<div class="container">
    <h1><i class="fas fa-trash-alt"></i> Supprimer un Foyer</h1>

    <?php if ($message): ?>
        <div class="<?= $msgType === 'success' ? 'msg-success' : 'msg-error' ?>">
            <?= htmlspecialchars($message) ?>
            <?php if ($msgType === 'success'): ?>
                <br><a href="foyers_liste.php" style="color:#065f46;font-weight:700">← Retour à la liste des foyers</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($foyer): ?>
    <div class="confirm-card">
        <div class="warn-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <h2>Êtes-vous sûr(e) ?</h2>
        <p style="color:#6c757d">Cette action est <strong>irréversible</strong>. Toutes les demandes liées à ce foyer seront également supprimées.</p>

        <div class="foyer-info">
            <table>
                <tr><td>Nom</td><td><?= htmlspecialchars($foyer['nom']) ?></td></tr>
                <tr><td>Ville</td><td><?= htmlspecialchars($foyer['ville']) ?></td></tr>
                <tr><td>Adresse</td><td><?= htmlspecialchars($foyer['adresse']) ?></td></tr>
                <tr><td>Capacité</td><td><?= $foyer['capacite'] ?> places</td></tr>
                <tr><td>Prix</td><td><?= number_format($foyer['prix_min'],0) ?> – <?= number_format($foyer['prix_max'],0) ?> DT/mois</td></tr>
            </table>
        </div>

        <form method="POST">
            <div class="btn-row">
                <a href="foyers_liste.php" class="btn-cancel"><i class="fas fa-arrow-left"></i> Annuler</a>
                <button type="submit" name="confirmer" value="1" class="btn-delete">
                    <i class="fas fa-trash"></i> Confirmer la suppression
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>
</main>
<?php include 'footer_partial.php'; ?>
</body>
</html>