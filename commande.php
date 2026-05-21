<?php
session_start();
require_once 'config.php';

// Rediriger si non connecté
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$erreur = '';
$succes = '';

// Récupérer les menus pour le select
$stmtMenus = $pdo->query("SELECT menu_id, titre, prix, nb_personne_minimum FROM menu WHERE quantite_restante > 0");
$tousMenus = $stmtMenus->fetchAll();

// Récupérer le menu sélectionné
$stmt = $pdo->prepare("SELECT * FROM menu WHERE menu_id = ?");
$stmt->execute([$id]);
$menu = $stmt->fetch();

// Récupérer les infos de l'utilisateur connecté
$stmt2 = $pdo->prepare("SELECT * FROM utilisateur WHERE utilisateur_id = ?");
$stmt2->execute([$_SESSION['user_id']]);
$user = $stmt2->fetch();

// Traitement de la commande
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adresse = htmlspecialchars(trim($_POST['adresse']));
    $ville = $_POST['ville'];
    $date = $_POST['date'];
    $heure = $_POST['heure'];
    $nb_personnes = (int)$_POST['nb_personnes'];
    $menu_id = (int)$_POST['menu_id'];

    // Récupérer le menu commandé
    $stmtM = $pdo->prepare("SELECT * FROM menu WHERE menu_id = ?");
    $stmtM->execute([$menu_id]);
    $menuCmd = $stmtM->fetch();

    if(empty($adresse) || empty($date) || empty($heure)) {
        $erreur = 'Veuillez remplir tous les champs obligatoires.';
    } elseif($nb_personnes < $menuCmd['nb_personne_minimum']) {
        $erreur = 'Le nombre minimum de personnes pour ce menu est '.$menuCmd['nb_personne_minimum'].'.';
    } else {
        // Calcul livraison
        $km = ['bordeaux'=>0,'merignac'=>8,'pessac'=>10,'talence'=>6,'begles'=>7,'autre'=>20];
        $distance = $km[$ville] ?? 0;
        $prix_livraison = $distance === 0 ? 0 : 5 + ($distance * 0.59);

        // Calcul réduction
        $reduction = $nb_personnes >= ($menuCmd['nb_personne_minimum'] + 5);
        $prix_menu = $reduction ? $menuCmd['prix'] * 0.9 : $menuCmd['prix'];

        // Numéro de commande unique
        $numero = 'VG-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -5));

        // Insérer la commande
        $stmtInsert = $pdo->prepare("INSERT INTO commande (numero_commande, date_prestation, heure_livraison, adresse_prestation, prix_menu, prix_livraison, nombre_personnes, statut, utilisateur_id, menu_id) VALUES (?, ?, ?, ?, ?, ?, ?, 'en attente', ?, ?)");
        $stmtInsert->execute([$numero, $date, $heure, $adresse, $prix_menu, $prix_livraison, $nb_personnes, $_SESSION['user_id'], $menu_id]);

        // Diminuer le stock
        $pdo->prepare("UPDATE menu SET quantite_restante = quantite_restante - 1 WHERE menu_id = ?")->execute([$menu_id]);

        $succes = 'Commande '.$numero.' validée ! Un email de confirmation vous a été envoyé.';
    }
}

$emojis = [1=>'🎄',2=>'🐣',3=>'💍',4=>'🥗',5=>'🌱',6=>'🏢'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Commander – Vite & Gourmand</title>
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
.commande-grid{display:grid;grid-template-columns:1fr 340px;gap:2rem}
.form-card{background:#fff;border:1px solid #f0e8d8;border-radius:16px;padding:2rem;margin-bottom:1.5rem}
.form-card h3{font-size:1rem;font-weight:700;margin-bottom:1.5rem;padding-bottom:.75rem;border-bottom:1px solid #f0e8d8}
.form-group{margin-bottom:1.25rem}
.form-group label{display:block;font-size:.82rem;font-weight:600;color:#555;margin-bottom:.5rem}
.form-group input,.form-group select{width:100%;padding:12px 14px;border:1px solid #e0d8cc;border-radius:8px;font-size:.95rem;background:#fffdf9;font-family:inherit}
.form-group input:focus,.form-group select:focus{outline:none;border-color:#f5a623}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.form-group input[readonly]{background:#f5f0e8;color:#888}
.info-livraison{background:#fff8e1;border-left:4px solid #f5a623;border-radius:0 8px 8px 0;padding:1rem;margin-bottom:1.25rem;font-size:.85rem;color:#666;line-height:1.7}
.recap{background:#fff;border:1px solid #f0e8d8;border-radius:16px;padding:1.5rem;position:sticky;top:80px;height:fit-content}
.recap h3{font-size:1rem;font-weight:700;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:1px solid #f0e8d8}
.recap-menu{display:flex;gap:1rem;align-items:center;margin-bottom:1.25rem;padding-bottom:1.25rem;border-bottom:1px solid #f0e8d8}
.recap-emoji{width:48px;height:48px;background:linear-gradient(135deg,#f5a623,#e07510);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.5rem}
.recap-menu-info h4{font-size:.9rem;font-weight:700}
.recap-menu-info p{font-size:.8rem;color:#888}
.recap-ligne{display:flex;justify-content:space-between;font-size:.85rem;padding:5px 0;color:#555}
.recap-ligne.reduction{color:#2e7d32}
.recap-ligne.total{font-weight:700;font-size:1rem;color:#2d2d2d;border-top:1px solid #f0e8d8;margin-top:.5rem;padding-top:.75rem}
.btn-commander{width:100%;padding:14px;background:#f5a623;color:#1a1a1a;border:none;border-radius:8px;font-size:1rem;font-weight:700;cursor:pointer;margin-top:1.25rem;font-family:inherit}
.btn-commander:hover{background:#e09510}
.alert{padding:12px 14px;border-radius:8px;font-size:.85rem;margin-bottom:1.25rem}
.alert.success{background:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9}
.alert.error{background:#fce4e4;color:#c62828;border:1px solid #ffcdd2}
.conditions-important{background:#fce4e4;border-left:4px solid #c62828;border-radius:0 8px 8px 0;padding:1rem;margin-bottom:1.5rem}
.conditions-important h4{font-size:.85rem;font-weight:700;color:#c62828;margin-bottom:.4rem}
.conditions-important p{font-size:.82rem;color:#666;line-height:1.7}
footer{background:#1a1a1a;color:#ccc;padding:2rem;text-align:center;font-size:.85rem;margin-top:3rem}
footer a{color:#888;margin:0 .5rem}
footer a:hover{color:#f5a623}
@media(max-width:768px){.commande-grid{grid-template-columns:1fr}.recap{position:static}.form-row{grid-template-columns:1fr}}
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
  <h1>Votre <span>commande</span></h1>
  <p>Remplissez les informations de votre prestation</p>
</div>

<div class="container">
  <?php if($erreur): ?>
    <div class="alert error"><?= $erreur ?></div>
  <?php endif; ?>
  <?php if($succes): ?>
    <div class="alert success"><?= $succes ?></div>
  <?php endif; ?>

  <form method="POST" action="commande.php?id=<?= $id ?>">
    <div class="commande-grid">
      <div>
        <div class="form-card">
          <h3>👤 Informations personnelles</h3>
          <div class="form-row">
            <div class="form-group">
              <label>Prénom</label>
              <input type="text" value="<?= htmlspecialchars($user['prenom']) ?>" readonly>
            </div>
            <div class="form-group">
              <label>Nom</label>
              <input type="text" value="<?= htmlspecialchars($user['nom']) ?>" readonly>
            </div>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
          </div>
          <div class="form-group">
            <label>Téléphone</label>
            <input type="tel" value="<?= htmlspecialchars($user['telephone']) ?>" readonly>
          </div>
        </div>

        <div class="form-card">
          <h3>📍 Informations de la prestation</h3>
          <div class="info-livraison">🚚 Livraison gratuite à Bordeaux. Hors Bordeaux : <strong>5€ + 0,59€/km</strong></div>
          <div class="form-group">
            <label>Adresse de livraison</label>
            <input type="text" name="adresse" placeholder="12 rue des Fleurs" required>
          </div>
          <div class="form-group">
            <label>Ville</label>
            <select name="ville" onchange="calculerPrix()">
              <option value="bordeaux">Bordeaux (livraison gratuite)</option>
              <option value="merignac">Mérignac (~8km)</option>
              <option value="pessac">Pessac (~10km)</option>
              <option value="talence">Talence (~6km)</option>
              <option value="begles">Bègles (~7km)</option>
              <option value="autre">Autre ville (~20km)</option>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Date de la prestation</label>
              <input type="date" name="date" required>
            </div>
            <div class="form-group">
              <label>Heure souhaitée</label>
              <input type="time" name="heure" value="12:00" required>
            </div>
          </div>
        </div>

        <div class="form-card">
          <h3>🍽️ Menu & nombre de personnes</h3>
          <div class="form-group">
            <label>Menu choisi</label>
            <select name="menu_id" id="menu-select" onchange="changerMenu()">
              <?php foreach($tousMenus as $m): ?>
                <option value="<?= $m['menu_id'] ?>" 
                  data-prix="<?= $m['prix'] ?>" 
                  data-min="<?= $m['nb_personne_minimum'] ?>"
                  data-titre="<?= htmlspecialchars($m['titre']) ?>"
                  <?= $m['menu_id']==$id?'selected':'' ?>>
                  <?= htmlspecialchars($m['titre']) ?> – <?= $m['prix'] ?>€ (<?= $m['nb_personne_minimum'] ?> pers. min.)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Nombre de personnes</label>
            <input type="number" name="nb_personnes" id="nb-personnes" value="<?= $menu['nb_personne_minimum'] ?>" min="<?= $menu['nb_personne_minimum'] ?>" oninput="calculerPrix()">
            <div style="font-size:.78rem;color:#888;margin-top:.3rem" id="personnes-hint"></div>
          </div>
          <?php if($menu): ?>
          <div class="conditions-important">
            <h4>⚠️ Conditions de ce menu</h4>
            <p id="conditions-texte"><?= htmlspecialchars($menu['conditions']) ?></p>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="recap">
        <h3>📋 Récapitulatif</h3>
        <div class="recap-menu">
          <div class="recap-emoji" id="recap-emoji"><?= $emojis[$id] ?? '🍽️' ?></div>
          <div class="recap-menu-info">
            <h4 id="recap-titre"><?= htmlspecialchars($menu['titre'] ?? '') ?></h4>
            <p id="recap-personnes"><?= $menu['nb_personne_minimum'] ?? '' ?> personnes minimum</p>
          </div>
        </div>
        <div class="recap-ligne"><span>Prix menu</span><span id="recap-prix-menu"><?= $menu['prix'] ?? 0 ?> €</span></div>
        <div class="recap-ligne"><span>Livraison</span><span id="recap-livraison">Gratuite</span></div>
        <div class="recap-ligne reduction" id="recap-reduction-ligne" style="display:none"><span>Réduction −10%</span><span id="recap-reduction">0 €</span></div>
        <div class="recap-ligne total"><span>Total</span><span id="recap-total"><?= $menu['prix'] ?? 0 ?> €</span></div>
        <button type="submit" class="btn-commander">✓ Valider la commande</button>
      </div>
    </div>
  </form>
</div>

<footer>
  <span>© 2024 Vite & Gourmand</span>
  <a href="mentions-legales.html">Mentions légales</a>
  <a href="cgv.html">CGV</a>
</footer>

<script>
const km = {bordeaux:0,merignac:8,pessac:10,talence:6,begles:7,autre:20};
const emojis = {1:'🎄',2:'🐣',3:'💍',4:'🥗',5:'🌱',6:'🏢'};

function changerMenu(){
  const sel = document.getElementById('menu-select');
  const opt = sel.options[sel.selectedIndex];
  const min = parseInt(opt.dataset.min);
  document.getElementById('nb-personnes').value = min;
  document.getElementById('nb-personnes').min = min;
  calculerPrix();
}

function calculerPrix(){
  const sel = document.getElementById('menu-select');
  const opt = sel.options[sel.selectedIndex];
  const prix = parseFloat(opt.dataset.prix);
  const min = parseInt(opt.dataset.min);
  const titre = opt.dataset.titre;
  const menuId = parseInt(sel.value);
  const nb = parseInt(document.getElementById('nb-personnes').value)||min;
  const ville = document.querySelector('select[name="ville"]').value;
  const distance = km[ville]||0;
  const prixLivraison = distance===0?0:5+(distance*0.59);
  const reduction = nb>=(min+5);
  const prixMenu = reduction?prix*0.9:prix;
  const total = prixMenu+prixLivraison;

  const hint = document.getElementById('personnes-hint');
  if(nb<min){hint.textContent='⚠️ Minimum '+min+' personnes';hint.style.color='#c62828';}
  else if(reduction){hint.textContent='🎉 Réduction 10% appliquée !';hint.style.color='#2e7d32';}
  else{hint.textContent='Réduction à partir de '+(min+5)+' personnes';hint.style.color='#888';}

  document.getElementById('recap-emoji').textContent = emojis[menuId]||'🍽️';
  document.getElementById('recap-titre').textContent = titre;
  document.getElementById('recap-personnes').textContent = nb+' personne(s)';
  document.getElementById('recap-prix-menu').textContent = prixMenu.toFixed(2)+' €';
  document.getElementById('recap-livraison').textContent = prixLivraison===0?'Gratuite':prixLivraison.toFixed(2)+' €';
  document.getElementById('recap-total').textContent = total.toFixed(2)+' €';

  if(reduction){
    document.getElementById('recap-reduction-ligne').style.display='flex';
    document.getElementById('recap-reduction').textContent='-'+(prix*0.1).toFixed(2)+' €';
  } else {
    document.getElementById('recap-reduction-ligne').style.display='none';
  }
}
calculerPrix();
</script>
</body>
</html>