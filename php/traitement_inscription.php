<?php
/**
 * traitement_inscription.php
 * Traitement du formulaire d'inscription candidat
 * Validation PHP + Insertion PDO (paramètres nommés)
 */
require_once 'connexion_bd.php';

$errors  = [];
$success = false;
$data    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDB();

    // ——— 1. Récupération & nettoyage ———
    $data = [
        'prenom'        => trim($_POST['prenom']        ?? ''),
        'nom'           => trim($_POST['nom']           ?? ''),
        'date_naissance'=> trim($_POST['naissance']     ?? ''),
        'email'         => trim($_POST['email']         ?? ''),
        'telephone'     => trim($_POST['telephone']     ?? ''),
        'adresse'       => trim($_POST['adresse']       ?? ''),
        'etablissement' => trim($_POST['etablissement'] ?? ''),
        'niveau'        => trim($_POST['niveau']        ?? ''),
        'num_etudiant'  => trim($_POST['num_etudiant']  ?? ''),
        'cin'           => trim($_POST['cin']           ?? ''),
        'foyer_id'      => (int)($_POST['foyer']        ?? 0),
        'type_chambre'  => trim($_POST['type_chambre']  ?? ''),
        'date_arrivee'  => trim($_POST['arrivee']       ?? ''),
        'duree_mois'    => (int)($_POST['duree']        ?? 0),
        'mot_de_passe'  => $_POST['password']           ?? '',
        'confirm_pass'  => $_POST['confirm_password']   ?? '',
    ];

    // ——— 2. Validation PHP côté serveur ———

    // Prénom / Nom
    if (empty($data['prenom']) || strlen($data['prenom']) < 2)
        $errors['prenom'] = 'Le prénom est obligatoire (min. 2 caractères).';
    if (empty($data['nom']) || strlen($data['nom']) < 2)
        $errors['nom'] = 'Le nom est obligatoire (min. 2 caractères).';

    // Date de naissance (doit avoir au moins 16 ans)
    if (empty($data['date_naissance'])) {
        $errors['date_naissance'] = 'La date de naissance est obligatoire.';
    } else {
        $dob = new DateTime($data['date_naissance']);
        $age = $dob->diff(new DateTime())->y;
        if ($age < 16) $errors['date_naissance'] = 'Vous devez avoir au moins 16 ans.';
    }

    // Email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'Adresse email invalide.';
    else {
        // Unicité
        $chk = $pdo->prepare("SELECT COUNT(*) FROM candidats WHERE email = :email");
        $chk->execute([':email' => $data['email']]);
        if ((int)$chk->fetchColumn() > 0)
            $errors['email'] = 'Cette adresse email est déjà utilisée.';
    }

    // Téléphone tunisien : 8 chiffres ou +216 suivi de 8 chiffres
    if (!preg_match('/^(\+216)?[2-9][0-9]{7}$/', preg_replace('/\s/', '', $data['telephone'])))
        $errors['telephone'] = 'Numéro de téléphone invalide (format tunisien : 8 chiffres ou +216XXXXXXXX).';

    // Adresse
    if (empty($data['adresse']) || strlen($data['adresse']) < 5)
        $errors['adresse'] = 'L\'adresse est obligatoire (min. 5 caractères).';

    // Établissement
    if (empty($data['etablissement']))
        $errors['etablissement'] = 'L\'établissement est obligatoire.';

    // Niveau
    $niveauxValides = ['L1','L2','L3','CP1','CP2','CI1','CI2','CI3','M1','M2','Doctorat'];
    if (!in_array($data['niveau'], $niveauxValides))
        $errors['niveau'] = 'Niveau d\'études invalide.';

    // Numéro étudiant
    if (empty($data['num_etudiant']))
        $errors['num_etudiant'] = 'Le numéro étudiant est obligatoire.';
    else {
        $chk2 = $pdo->prepare("SELECT COUNT(*) FROM candidats WHERE num_etudiant = :ne");
        $chk2->execute([':ne' => $data['num_etudiant']]);
        if ((int)$chk2->fetchColumn() > 0)
            $errors['num_etudiant'] = 'Ce numéro étudiant est déjà enregistré.';
    }

    // CIN (8 chiffres)
    if (!preg_match('/^[0-9]{8}$/', $data['cin']))
        $errors['cin'] = 'Le numéro de CIN doit contenir exactement 8 chiffres.';

    // Mot de passe (min 8 cars, 1 maj, 1 chiffre, 1 spécial)
    if (!preg_match('/^(?=.*[A-Z])(?=.*[0-9])(?=.*[@#$!%*?&]).{8,}$/', $data['mot_de_passe']))
        $errors['mot_de_passe'] = 'Le mot de passe doit contenir au moins 8 caractères, 1 majuscule, 1 chiffre et 1 caractère spécial (@#$!%*?&).';

    if ($data['mot_de_passe'] !== $data['confirm_pass'])
        $errors['confirm_pass'] = 'Les mots de passe ne correspondent pas.';

    // Foyer
    if ($data['foyer_id'] <= 0)
        $errors['foyer_id'] = 'Veuillez sélectionner un foyer.';

    // Type chambre
    if (!in_array($data['type_chambre'], ['simple','double','studio']))
        $errors['type_chambre'] = 'Type de chambre invalide.';

    // Date d'arrivée (doit être dans le futur)
    if (empty($data['date_arrivee'])) {
        $errors['date_arrivee'] = 'La date d\'arrivée est obligatoire.';
    } elseif (strtotime($data['date_arrivee']) < strtotime('today')) {
        $errors['date_arrivee'] = 'La date d\'arrivée doit être dans le futur.';
    }

    // Durée
    if ($data['duree_mois'] <= 0)
        $errors['duree_mois'] = 'La durée de séjour est obligatoire.';

    // Gestion photo
    $photoName = 'default-avatar.jpg';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
            $errors['photo'] = 'Format de photo non autorisé (jpg/png/webp).';
        } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $errors['photo'] = 'Photo trop volumineuse (max 2 Mo).';
        } else {
            $photoName = uniqid('candidat_') . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], '../images/' . $photoName);
        }
    }

    // ——— 3. Insertion si pas d'erreurs ———
    if (empty($errors)) {
        // Insérer le candidat
        $sqlC = "INSERT INTO candidats
                    (prenom, nom, date_naissance, email, telephone, adresse, etablissement, niveau, num_etudiant, cin, photo, mot_de_passe)
                 VALUES
                    (:prenom, :nom, :date_naissance, :email, :telephone, :adresse, :etablissement, :niveau, :num_etudiant, :cin, :photo, :mdp)";
        $stmtC = $pdo->prepare($sqlC);
        $stmtC->execute([
            ':prenom'         => $data['prenom'],
            ':nom'            => $data['nom'],
            ':date_naissance' => $data['date_naissance'],
            ':email'          => $data['email'],
            ':telephone'      => preg_replace('/\s/','',$data['telephone']),
            ':adresse'        => $data['adresse'],
            ':etablissement'  => $data['etablissement'],
            ':niveau'         => $data['niveau'],
            ':num_etudiant'   => $data['num_etudiant'],
            ':cin'            => $data['cin'],
            ':photo'          => $photoName,
            ':mdp'            => password_hash($data['mot_de_passe'], PASSWORD_DEFAULT),
        ]);
        $candidatId = (int)$pdo->lastInsertId();

        // Insérer la demande
        $sqlD = "INSERT INTO demandes (candidat_id, foyer_id, type_chambre, date_arrivee, duree_mois)
                 VALUES (:cid, :fid, :type, :arrivee, :duree)";
        $stmtD = $pdo->prepare($sqlD);
        $stmtD->execute([
            ':cid'    => $candidatId,
            ':fid'    => $data['foyer_id'],
            ':type'   => $data['type_chambre'],
            ':arrivee'=> $data['date_arrivee'],
            ':duree'  => $data['duree_mois'],
        ]);

        $success = true;
    }
}

// Récupérer la liste des foyers pour le formulaire
$pdo    = getDB();
$foyers = $pdo->query("SELECT id, nom, ville FROM foyers ORDER BY ville, nom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — FoyerConnect</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .inscription-grid { display:grid; grid-template-columns:2fr 1fr; gap:2rem; margin-top:2rem; }
        .form-section { background:#fff; padding:2rem; border-radius:16px; box-shadow:0 2px 8px rgba(0,0,0,.06); margin-bottom:2rem; }
        .form-section h2 { margin-bottom:1.5rem; font-size:1.2rem; color:#0066cc; }
        .form-group { margin-bottom:1.4rem; }
        .form-group label { font-weight:600; font-size:.9rem; display:block; margin-bottom:.5rem; }
        .form-group input, .form-group select { width:100%; padding:.85rem 1.2rem; border:1.5px solid #dee2e6; border-radius:30px; font-size:.95rem; font-family:'Poppins',sans-serif; transition:all .2s; background:#fff; }
        .form-group input:focus, .form-group select:focus { outline:none; border-color:#0066cc; box-shadow:0 0 0 3px rgba(0,102,204,.12); }
        .form-group input.error-field { border-color:#ef476f; }
        .field-error { color:#ef476f; font-size:.82rem; margin-top:.3rem; padding-left:.5rem; display:block; }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .btn-submit { background:linear-gradient(135deg,#0066cc,#0052a3); color:#fff; border:none; padding:1rem 2rem; border-radius:30px; font-size:1rem; font-weight:700; cursor:pointer; width:100%; margin-top:1rem; }
        .resume-card { background:#fff; padding:2rem; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,.08); position:sticky; top:100px; }
        .success-box { background:#d1fae5; border:1px solid #6ee7b7; color:#065f46; padding:2rem; border-radius:16px; text-align:center; margin-bottom:2rem; }
        .required { color:#ef476f; margin-left:2px; }
        @media(max-width:768px){ .inscription-grid{grid-template-columns:1fr} .form-row{grid-template-columns:1fr} }
    </style>
</head>
<body>
<?php include 'header_partial.php'; ?>
<main>
<div class="container">
    <h1><i class="fas fa-user-plus"></i> Demande d'inscription</h1>

    <?php if ($success): ?>
    <div class="success-box">
        <div style="font-size:3rem">✅</div>
        <h2>Inscription soumise avec succès !</h2>
        <p>Votre demande a été enregistrée. Vous recevrez une réponse par email dans les 48h.</p>
        <a href="../index.html" style="display:inline-block;margin-top:1rem;background:#065f46;color:#fff;padding:.8rem 2rem;border-radius:30px;font-weight:700;text-decoration:none">
            ← Retour à l'accueil
        </a>
    </div>
    <?php else: ?>

    <div class="inscription-grid">
        <div>
            <form method="POST" enctype="multipart/form-data" id="inscriptionForm" novalidate>
                <!-- INFORMATIONS PERSONNELLES -->
                <div class="form-section">
                    <h2><i class="fas fa-user"></i> Informations personnelles</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Prénom <span class="required">*</span></label>
                            <input type="text" name="prenom" id="prenom" value="<?= htmlspecialchars($data['prenom'] ?? '') ?>"
                                   class="<?= isset($errors['prenom'])?'error-field':'' ?>" placeholder="Votre prénom">
                            <?php if(isset($errors['prenom'])): ?><span class="field-error"><?= htmlspecialchars($errors['prenom']) ?></span><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label>Nom <span class="required">*</span></label>
                            <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($data['nom'] ?? '') ?>"
                                   class="<?= isset($errors['nom'])?'error-field':'' ?>" placeholder="Votre nom">
                            <?php if(isset($errors['nom'])): ?><span class="field-error"><?= htmlspecialchars($errors['nom']) ?></span><?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Date de naissance <span class="required">*</span></label>
                        <input type="date" name="naissance" id="naissance" value="<?= htmlspecialchars($data['date_naissance'] ?? '') ?>"
                               class="<?= isset($errors['date_naissance'])?'error-field':'' ?>">
                        <?php if(isset($errors['date_naissance'])): ?><span class="field-error"><?= htmlspecialchars($errors['date_naissance']) ?></span><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <input type="email" name="email" id="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>"
                               class="<?= isset($errors['email'])?'error-field':'' ?>" placeholder="prenom.nom@example.com">
                        <?php if(isset($errors['email'])): ?><span class="field-error"><?= htmlspecialchars($errors['email']) ?></span><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Téléphone <span class="required">*</span></label>
                        <input type="tel" name="telephone" id="telephone" value="<?= htmlspecialchars($data['telephone'] ?? '') ?>"
                               class="<?= isset($errors['telephone'])?'error-field':'' ?>" placeholder="+216 XX XXX XXX">
                        <?php if(isset($errors['telephone'])): ?><span class="field-error"><?= htmlspecialchars($errors['telephone']) ?></span><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Adresse actuelle <span class="required">*</span></label>
                        <input type="text" name="adresse" id="adresse" value="<?= htmlspecialchars($data['adresse'] ?? '') ?>"
                               class="<?= isset($errors['adresse'])?'error-field':'' ?>" placeholder="Numéro, rue, ville">
                        <?php if(isset($errors['adresse'])): ?><span class="field-error"><?= htmlspecialchars($errors['adresse']) ?></span><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>CIN <span class="required">*</span></label>
                        <input type="text" name="cin" id="cin" value="<?= htmlspecialchars($data['cin'] ?? '') ?>"
                               class="<?= isset($errors['cin'])?'error-field':'' ?>" placeholder="8 chiffres" maxlength="8">
                        <?php if(isset($errors['cin'])): ?><span class="field-error"><?= htmlspecialchars($errors['cin']) ?></span><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Photo d'identité <span style="font-weight:400;color:#6c757d">(jpg/png, max 2Mo)</span></label>
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" style="border-radius:12px;padding:.6rem">
                        <?php if(isset($errors['photo'])): ?><span class="field-error"><?= htmlspecialchars($errors['photo']) ?></span><?php endif; ?>
                    </div>
                </div>

                <!-- INFORMATIONS ACADÉMIQUES -->
                <div class="form-section">
                    <h2><i class="fas fa-graduation-cap"></i> Informations académiques</h2>
                    <div class="form-group">
                        <label>Établissement <span class="required">*</span></label>
                        <input type="text" name="etablissement" id="etablissement" value="<?= htmlspecialchars($data['etablissement'] ?? '') ?>"
                               class="<?= isset($errors['etablissement'])?'error-field':'' ?>" placeholder="Votre université / école">
                        <?php if(isset($errors['etablissement'])): ?><span class="field-error"><?= htmlspecialchars($errors['etablissement']) ?></span><?php endif; ?>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Niveau d'études <span class="required">*</span></label>
                            <select name="niveau" id="niveau">
                                <?php foreach(['L1'=>'Licence 1','L2'=>'Licence 2','L3'=>'Licence 3','CP1'=>'Cycle Prép. 1','CP2'=>'Cycle Prép. 2','CI1'=>'Ingénieur 1','CI2'=>'Ingénieur 2','CI3'=>'Ingénieur 3','M1'=>'Master 1','M2'=>'Master 2','Doctorat'=>'Doctorat'] as $k=>$v): ?>
                                    <option value="<?= $k ?>" <?= (($data['niveau']??'')===$k)?'selected':'' ?>><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>N° étudiant <span class="required">*</span></label>
                            <input type="text" name="num_etudiant" id="num_etudiant" value="<?= htmlspecialchars($data['num_etudiant'] ?? '') ?>"
                                   class="<?= isset($errors['num_etudiant'])?'error-field':'' ?>" placeholder="ex: ETU20240001">
                            <?php if(isset($errors['num_etudiant'])): ?><span class="field-error"><?= htmlspecialchars($errors['num_etudiant']) ?></span><?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- HÉBERGEMENT -->
                <div class="form-section">
                    <h2><i class="fas fa-hotel"></i> Hébergement souhaité</h2>
                    <div class="form-group">
                        <label>Foyer souhaité <span class="required">*</span></label>
                        <select name="foyer" id="foyer" class="<?= isset($errors['foyer_id'])?'error-field':'' ?>">
                            <option value="">-- Choisir un foyer --</option>
                            <?php foreach($foyers as $f): ?>
                                <option value="<?= $f['id'] ?>" <?= (($data['foyer_id']??0)===$f['id'])?'selected':'' ?>>
                                    <?= htmlspecialchars($f['nom']) ?> (<?= htmlspecialchars($f['ville']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if(isset($errors['foyer_id'])): ?><span class="field-error"><?= htmlspecialchars($errors['foyer_id']) ?></span><?php endif; ?>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Type de chambre <span class="required">*</span></label>
                            <select name="type_chambre" id="type_chambre">
                                <option value="simple"  <?= (($data['type_chambre']??'')==='simple')?'selected':'' ?>>Chambre simple</option>
                                <option value="double"  <?= (($data['type_chambre']??'')==='double')?'selected':'' ?>>Chambre double</option>
                                <option value="studio"  <?= (($data['type_chambre']??'')==='studio')?'selected':'' ?>>Studio</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Durée du séjour <span class="required">*</span></label>
                            <select name="duree" id="duree">
                                <?php foreach([1=>'1 mois',3=>'3 mois',6=>'6 mois',9=>'9 mois',12=>'1 an'] as $k=>$v): ?>
                                    <option value="<?= $k ?>" <?= (($data['duree_mois']??0)===$k)?'selected':'' ?>><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Date d'arrivée souhaitée <span class="required">*</span></label>
                        <input type="date" name="arrivee" id="arrivee" value="<?= htmlspecialchars($data['date_arrivee'] ?? '') ?>"
                               class="<?= isset($errors['date_arrivee'])?'error-field':'' ?>">
                        <?php if(isset($errors['date_arrivee'])): ?><span class="field-error"><?= htmlspecialchars($errors['date_arrivee']) ?></span><?php endif; ?>
                    </div>
                </div>

                <!-- MOT DE PASSE -->
                <div class="form-section">
                    <h2><i class="fas fa-lock"></i> Sécurité du compte</h2>
                    <div class="form-group">
                        <label>Mot de passe <span class="required">*</span></label>
                        <input type="password" name="password" id="password"
                               class="<?= isset($errors['mot_de_passe'])?'error-field':'' ?>" placeholder="Min. 8 car. + 1 maj + 1 chiffre + 1 spécial">
                        <?php if(isset($errors['mot_de_passe'])): ?><span class="field-error"><?= htmlspecialchars($errors['mot_de_passe']) ?></span><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Confirmer le mot de passe <span class="required">*</span></label>
                        <input type="password" name="confirm_password" id="confirm_password"
                               class="<?= isset($errors['confirm_pass'])?'error-field':'' ?>" placeholder="Répéter le mot de passe">
                        <?php if(isset($errors['confirm_pass'])): ?><span class="field-error"><?= htmlspecialchars($errors['confirm_pass']) ?></span><?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Soumettre ma demande</button>
            </form>
        </div>

        <div>
            <div class="resume-card">
                <h3><i class="fas fa-clipboard-list"></i> Résumé</h3>
                <p><strong>Foyer :</strong> <span id="resume-foyer">—</span></p>
                <p><strong>Type :</strong> <span id="resume-type">—</span></p>
                <p><strong>Durée :</strong> <span id="resume-duree">—</span></p>
                <p><strong>Arrivée :</strong> <span id="resume-arrivee">—</span></p>
                <hr style="margin:1rem 0">
                <p><em>Le paiement en ligne sera disponible après validation de votre dossier.</em></p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
</main>
<?php include 'footer_partial.php'; ?>

<script>
// ——— Mise à jour du résumé en temps réel ———
const foyerSel = document.getElementById('foyer');
const typeSel  = document.getElementById('type_chambre');
const dureeSel = document.getElementById('duree');
const arriveeI = document.getElementById('arrivee');

function updateResume() {
    document.getElementById('resume-foyer').textContent  = foyerSel ? (foyerSel.options[foyerSel.selectedIndex]?.text || '—') : '—';
    document.getElementById('resume-type').textContent   = typeSel  ? (typeSel.options[typeSel.selectedIndex]?.text  || '—') : '—';
    document.getElementById('resume-duree').textContent  = dureeSel ? (dureeSel.options[dureeSel.selectedIndex]?.text|| '—') : '—';
    document.getElementById('resume-arrivee').textContent= arriveeI ? (arriveeI.value || '—') : '—';
}
[foyerSel,typeSel,dureeSel,arriveeI].forEach(el => el?.addEventListener('change', updateResume));
updateResume();
</script>
</body>
</html>