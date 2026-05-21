<?php
session_start();
require_once 'config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Récupérer les infos utilisateur
$stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE utilisateur_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Récupérer les commandes
$stmt2 = $pdo->prepare("SELECT c.*, m.titre as menu_titre FROM commande c JOIN menu m ON c.menu_id = m.menu_id WHERE c.utilisateur_id = ? ORDER BY c.commande_id DESC");
$stmt2->execute([$_SESSION['user_id']]);
$commandes = $stmt2->fetchAll();

$succes = '';
$erreur = '';

// Mise à jour infos personnelles
if(isset($_POST['action']) && $_POST['action'] === 'update') {
    $nom = htmlspecialchars(trim($_POST['nom']));
    $prenom = htmlspecialchars(trim($_POST['prenom']));
    $telephone = htmlspecialchars(trim($_POST['telephone']));
    $adresse = htmlspecialchars(trim($_POST['adresse']));

    $stmt3 = $pdo->prepare("UPDATE utilisateur SET nom=?, prenom=?, telephone=?, adresse_postale=? WHERE utilisateur_id=?");
    $stmt3->execute([$nom, $prenom, $telephone, $adresse, $_SESSION['user_id']]);
    $succes = 'Informations mises à jour avec succès !';

    // Rafraîchir les données
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
}

// Annulation commande
if(isset($_GET['annuler']) && is_numeric($_GET['annuler'])) {
    $stmt4 = $pdo->prepare("UPDATE commande SET statut='annulée' WHERE commande_id=? AND utilisateur_id=? AND statut='en attente'");
    $stmt4->execute([$_GET['annuler'], $_SESSION['user_id']]);
    header('Location: mon-compte.php');
    exit;
}

$statut_colors = [
    'en attente' => '#888',
    'acceptée' => '#2e7d32',
    'en préparation' => '#f5a623',
    'en cours de livraison' => '#1565c0',
    'livré' => '#2e7d32',
    'en attente du retour de matériel' => '#c62828',
    'terminée' => '#2e7d32',
    'annulée' => '#c62828'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mon Compte – Vite & Gourmand</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',sans-serif;color:#2d2d2d;background:#fffdf9}
a{text-decoration:none;color:inherit}
nav{background:#1a1a1a;padding:0 2rem;display:flex;align-items:center;justify-content:space-between;height:64px;position:sticky;top:0;z-index:100}
.logo{color:#f5a623;font-size:1.4rem;font-weight:700}
.logo span{color:#fff}
.nav-links{display:flex;gap:2rem;align-items:center}
.nav-links a{color:#ccc;font-size:.9rem;transition:color .2s}
.nav-links a:hover{color:#f5a623}
.btn-login{background:#f5a623;color:#1a1a1a!important;padding:8px 18px;border-radius:6px;font-weight:600;font-size:.85rem!important}
.page-header{background:linear-gradient(135deg,#1a1a1a,#2d1a00);color:#fff;padding:60px 2rem;text-align:center}
.page-header h1{font-size:2.5rem;font-weight:800;margin-bottom:.5rem}
.page-header h1 span{color:#f5a623}
.page-header p{color:#ccc;font-size:1rem}
.container{max-width:1000px;margin:0 auto;padding:3rem 2rem}
.tabs{display:flex;background:#f5f0e8;border-radius:8px;padding:4px;margin-bottom:2rem;width:fit-content}
.tab{padding:10px 24px;font-size:.9rem;font-weight:600;border-radius:6px;cursor:pointer;color:#888;border:none;background:transparent;transition:all .2s}
.tab.active{background:#fff;color:#2d2d2d;box-shadow:0 1px 4px rgba(0,0,0,.1)}
.section{display:none}
.section.visible{display:block}
.card{background:#fff;border:1px solid #f0e8d8;border-radius:16px;padding:2rem;margin-bottom:1.5rem}
.card h3{font-size:1rem;font-weight:700;margin-bottom:1.5rem;padding-bottom:.75rem;border-bottom:1px solid #f0e8d8}
.form-group{margin-bottom:1.25rem}
.form-group label{display:block;font-size:.82rem;font-weight:600;color:#555;margin-bottom:.5rem}
.form-group input{width:100%;padding:12px 14px;border:1px solid #e0d8cc;border-radius:8px;font-size:.95rem;background:#fffdf9;font-family:inherit}
.form-group input:focus{outline:none;border-color:#f5a623}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.btn-submit{padding:12px 24px;background:#f5a623;color:#1a1a1a;border:none;border-radius:8px;font-size:.95rem;font-weight:700;cursor:pointer;font-family:inherit}
.btn-submit:hover{background:#e09510}
.alert{padding:12px 14px;border-radius:8px;font-size:.85rem;margin-bottom:1.25rem}
.alert.success{background:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9}
.alert.error{background:#fce4e4;color:#c62828;border:1px solid #ffcdd2}
.commande-item{background:#fff;border:1px solid #f0e8d8;border-radius:12px;padding:1.5rem;margin-bottom:1rem}
.commande-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem}
.commande-num{font-size:.9rem;font-weight:700}
.commande-statut{font-size:.75rem;font-weight:700;padding:4px 12px;border-radius:99px;color:#fff}
.commande-details{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;font-size:.85rem;color:#555;margin-bottom:1rem}
.commande-detail-item span{display:block;font-size:.75rem;color:#999;margin-bottom:.2rem}
.commande-detail-item strong{color:#2d2d2d}
.btn-annuler{padding:8px 16px;background:transparent;border:1px solid #c62828;color:#c62828;border-radius:6px;font-size:.82rem;cursor:pointer;font-family:inherit}
.btn-annuler:hover{background:#fce4e4}
.empty{text-align:center;padding:3rem;color:#888}
.empty .icon{font-size:3rem;margin-bottom:1rem}
footer{background:#1a1a1a;color:#ccc;padding:2rem;text-align:center;font-size:.85rem;margin-top:3rem}
footer a{color:#888;margin:0 .5rem}
footer a:hover{color:#f5a623}
@media(max-width:768px){.commande-details{grid-template-columns:1fr}.form-row{grid-template-columns:1fr}}
</style>
</head>
<body>

<nav>
  <a href="index.html" class="logo">Vite <span>& Gourmand</span></a>
  <div class="nav-links">
    <a href="menus.php">Nos menus</a>
    <a href="contact.html">Contact</a>
    <a href="logout.php" class="btn-login">Déconnexion</a>
  </div>
</nav>

<div class="page-header">
  <h1>Mon <span>compte</span></h1>
  <p>Bonjour <?= htmlspecialchars($user['prenom']) ?> <?= htmlspecialchars($user['nom']) ?> !</p>
</div>

<div class="container">
  <div class="tabs">
    <button class="tab active" onclick="showTab('commandes')">Mes commandes</button>
    <button class="tab" onclick="showTab('profil')">Mon profil</button>
  </div>

  <!-- COMMANDES -->
  <div class="section visible" id="tab-commandes">
    <?php if(count($commandes) === 0): ?>
      <div class="empty">
        <div class="icon">🛒</div>
        <p>Vous n'avez pas encore de commande.</p>
        <a href="menus.php" style="color:#f5a623;font-weight:600;margin-top:1rem;display:inline-block">Découvrir nos menus →</a>
      </div>
    <?php else: ?>
      <?php foreach($commandes as $cmd): ?>
        <div class="commande-item">
          <div class="commande-header">
            <span class="commande-num">📦 <?= htmlspecialchars($cmd['numero_commande']) ?></span>
            <span class="commande-statut" style="background:<?= $statut_colors[$cmd['statut']] ?? '#888' ?>">
              <?= htmlspecialchars($cmd['statut']) ?>
            </span>
          </div>
          <div class="commande-details">
            <div class="commande-detail-item">
              <span>Menu</span>
              <strong><?= htmlspecialchars($cmd['menu_titre']) ?></strong>
            </div>
            <div class="commande-detail-item">
              <span>Date prestation</span>
              <strong><?= htmlspecialchars($cmd['date_prestation']) ?></strong>
            </div>
            <div class="commande-detail-item">
              <span>Total</span>
              <strong><?= number_format($cmd['prix_menu'] + $cmd['prix_livraison'], 2) ?> €</strong>
            </div>
            <div class="commande-detail-item">
              <span>Personnes</span>
              <strong><?= $cmd['nombre_personnes'] ?></strong>
            </div>
            <div class="commande-detail-item">
              <span>Livraison</span>
              <strong><?= $cmd['prix_livraison'] == 0 ? 'Gratuite' : $cmd['prix_livraison'].' €' ?></strong>
            </div>
            <div class="commande-detail-item">
              <span>Adresse</span>
              <strong><?= htmlspecialchars($cmd['adresse_prestation']) ?></strong>
            </div>
          </div>
          <?php if($cmd['statut'] === 'en attente'): ?>
            <a href="mon-compte.php?annuler=<?= $cmd['commande_id'] ?>" onclick="return confirm('Confirmer l\'annulation ?')">
              <button class="btn-annuler">Annuler la commande</button>
            </a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- PROFIL -->
  <div class="section" id="tab-profil">
    <?php if($succes): ?>
      <div class="alert success"><?= $succes ?></div>
    <?php endif; ?>
    <div class="card">
      <h3>👤 Mes informations</h3>
      <form method="POST" action="mon-compte.php">
        <input type="hidden" name="action" value="update">
        <div class="form-row">
          <div class="form-group">
            <label>Prénom</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
          </div>
          <div class="form-group">
            <label>Nom</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly style="background:#f5f0e8;color:#888">
        </div>
        <div class="form-group">
          <label>Téléphone</label>
          <input type="tel" name="telephone" value="<?= htmlspecialchars($user['telephone']) ?>">
        </div>
        <div class="form-group">
          <label>Adresse postale</label>
          <input type="text" name="adresse" value="<?= htmlspecialchars($user['adresse_postale']) ?>">
        </div>
        <button type="submit" class="btn-submit">Enregistrer les modifications</button>
      </form>
    </div>
  </div>
</div>

<footer>
  <span>© 2024 Vite & Gourmand</span>
  <a href="mentions-legales.html">Mentions légales</a>
  <a href="cgv.html">CGV</a>
</footer>

<script>
function showTab(tab) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('visible'));
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  document.getElementById('tab-'+tab).classList.add('visible');
  event.target.classList.add('active');
}
</script>
</body>
</html>