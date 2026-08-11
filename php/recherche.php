<?php
/**
 * foyers_liste.php
 * Affichage de la liste des foyers avec :
 *  - Classe PHP (Foyer)
 *  - Tableau d'objets
 *  - Fonction d'affichage HTML
 *  - Requête PDO : query() + fetchAll() + fetchObject()
 */
require_once 'connexion_bd.php';

/* ================================================================
   CLASSE Foyer — représente un enregistrement de la table foyers
   ================================================================ */
class Foyer {
    public int    $id;
    public string $nom;
    public string $ville;
    public string $adresse;
    public string $telephone;
    public string $email;
    public int    $capacite;
    public float  $prix_min;
    public float  $prix_max;
    
    public string $description;

    public function __construct(array $row) {
        $this->id          = (int)$row['id'];
        $this->nom         = htmlspecialchars($row['nom']);
        $this->ville       = htmlspecialchars($row['ville']);
        $this->adresse     = htmlspecialchars($row['adresse']);
        $this->telephone   = htmlspecialchars($row['telephone']);
        $this->email       = htmlspecialchars($row['email']);
        $this->capacite    = (int)$row['capacite'];
        $this->prix_min    = (float)$row['prix_min'];
        $this->prix_max    = (float)$row['prix_max'];
        
        $this->description = htmlspecialchars($row['description'] ?? '');
    }

    /** Retourne une plage de prix formatée */
    public function getPrixRange(): string {
        return number_format($this->prix_min, 0, ',', ' ')
             . ' – '
             . number_format($this->prix_max, 0, ',', ' ')
             . ' DT/mois';
    }
}

/* ================================================================
   RÉCUPÉRATION avec fetchAll() — renvoie un tableau associatif
   ================================================================ */
function getFoyersArray(): array {
    $pdo  = getDB();
    $stmt = $pdo->query("SELECT * FROM foyers ORDER BY ville, nom");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);          // fetchAll()

    // Transformer en tableau d'objets Foyer
    $foyers = [];
    foreach ($rows as $row) {
        $foyers[] = new Foyer($row);
    }
    return $foyers;
}

/* ================================================================
   RÉCUPÉRATION avec fetchObject() — démo alternative
   ================================================================ */
function getFoyerById(int $id): ?object {
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT * FROM foyers WHERE id = ?");
    $stmt->execute([$id]);
    $obj  = $stmt->fetchObject();                        // fetchObject()
    return $obj ?: null;
}

/* ================================================================
   FONCTION D'AFFICHAGE — parcourt le tableau et génère un tableau HTML
   ================================================================ */
function afficherTableauFoyers(array $foyers): void {
    if (empty($foyers)) {
        echo '<p class="no-data">Aucun foyer disponible pour le moment.</p>';
        return;
    }
    echo '<div class="table-responsive">';
    echo '<table>';
    echo '<thead><tr>
            <th>#</th>
            <th>Nom du foyer</th>
            <th>Ville</th>
            <th>Capacité</th>
            <th>Prix</th>
            <th>Contact</th>
            <th>Actions</th>
          </tr></thead>';
    echo '<tbody>';
    foreach ($foyers as $f) {
        echo '<tr>';
        echo '<td>' . $f->id . '</td>';
        echo '<td><img src="../images/' . $f->photo . '" alt="' . $f->nom . '" style="width:60px;height:45px;object-fit:cover;border-radius:6px;"></td>';
        echo '<td><strong>' . $f->nom . '</strong><br><small>' . $f->adresse . '</small></td>';
        echo '<td>' . $f->ville . '</td>';
        echo '<td>' . $f->capacite . ' places</td>';
        echo '<td>' . $f->getPrixRange() . '</td>';
        echo '<td>' . $f->telephone . '<br><a href="mailto:' . $f->email . '">' . $f->email . '</a></td>';
        echo '<td>
                <a href="foyer_modifier.php?id=' . $f->id . '" class="btn-action btn-edit"><i class="fas fa-edit"></i> Modifier</a>
                <a href="foyer_supprimer.php?id=' . $f->id . '" class="btn-action btn-delete" onclick="return confirm(\'Supprimer ce foyer ?\')"><i class="fas fa-trash"></i> Supprimer</a>
              </td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}

// ——— Récupération des données ———
$foyers = getFoyersArray();

// Filtre par ville (si paramètre GET)
$villeFiltre = isset($_GET['ville']) ? trim($_GET['ville']) : '';
if ($villeFiltre !== '') {
    $foyers = array_filter($foyers, fn($f) => strtolower($f->ville) === strtolower($villeFiltre));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Foyers — FoyerConnect</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .table-responsive { overflow-x: auto; margin-top: 2rem; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.07); }
        th { background: #0066cc; color: #fff; padding: 1rem; text-align: left; font-weight: 600; }
        td { padding: .85rem 1rem; border-bottom: 1px solid #e9ecef; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f0f6ff; }
        .btn-action { display: inline-block; padding: .35rem .85rem; border-radius: 30px; font-size: .82rem; font-weight: 600; text-decoration: none; margin-right: .3rem; }
        .btn-edit   { background: #0066cc; color: #fff; }
        .btn-delete { background: #ef476f; color: #fff; }
        .filter-bar { background: #fff; padding: 1.5rem 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.06); margin-bottom: 1.5rem; display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap; }
        .filter-bar label { font-weight: 600; font-size: .9rem; margin-bottom: .4rem; display: block; }
        .filter-bar select, .filter-bar input { padding: .7rem 1.2rem; border: 1px solid #dee2e6; border-radius: 30px; font-size: .95rem; }
        .filter-bar button { padding: .75rem 1.8rem; background: #0066cc; color: #fff; border: none; border-radius: 30px; font-weight: 700; cursor: pointer; }
        .no-data { text-align:center; padding: 3rem; color: #6c757d; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .btn-add { background: linear-gradient(135deg,#ff8c42,#e6732e); color: #fff; padding: .75rem 1.5rem; border-radius: 30px; font-weight: 700; text-decoration: none; }
    </style>
</head>
<body>
<?php include 'header_partial.php'; ?>
<main>
<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-hotel"></i> Gestion des Foyers</h1>
        <a href="foyer_ajouter.php" class="btn-add"><i class="fas fa-plus"></i> Ajouter un foyer</a>
    </div>

    <!-- Filtre par ville -->
    <form method="GET" class="filter-bar">
        <div>
            <label for="ville">Filtrer par ville</label>
            <select name="ville" id="ville">
                <option value="">Toutes les villes</option>
                <option value="Tunis"   <?= $villeFiltre==='Tunis'   ? 'selected':'' ?>>Tunis</option>
                <option value="Sfax"    <?= $villeFiltre==='Sfax'    ? 'selected':'' ?>>Sfax</option>
                <option value="Sousse"  <?= $villeFiltre==='Sousse'  ? 'selected':'' ?>>Sousse</option>
                <option value="Ariana"  <?= $villeFiltre==='Ariana'  ? 'selected':'' ?>>Ariana</option>
                <option value="Monastir"<?= $villeFiltre==='Monastir'? 'selected':'' ?>>Monastir</option>
            </select>
        </div>
        <button type="submit"><i class="fas fa-filter"></i> Filtrer</button>
        <?php if ($villeFiltre): ?>
            <a href="foyers_liste.php" style="color:#0066cc;font-weight:600;text-decoration:none;padding:.75rem 1rem;">✕ Réinitialiser</a>
        <?php endif; ?>
    </form>

    <p><strong><?= count($foyers) ?></strong> foyer(s) trouvé(s)</p>

    <?php afficherTableauFoyers($foyers); ?>
</div>
</main>
<?php include 'footer_partial.php'; ?>
</body>
</html>