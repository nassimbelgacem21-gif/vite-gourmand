<?php
require_once 'config.php';

// Récupérer les filtres
$prix_max = isset($_GET['prix_max']) ? (float)$_GET['prix_max'] : 9999;
$prix_min = isset($_GET['prix_min']) ? (float)$_GET['prix_min'] : 0;
$theme = isset($_GET['theme']) ? $_GET['theme'] : '';
$regime = isset($_GET['regime']) ? $_GET['regime'] : '';
$personnes = isset($_GET['personnes']) ? (int)$_GET['personnes'] : 0;

// Construire la requête
$sql = "SELECT m.*, t.libelle as theme, r.libelle as regime 
        FROM menu m 
        JOIN theme t ON m.theme_id = t.theme_id 
        JOIN regime r ON m.regime_id = r.regime_id 
        WHERE m.prix <= :prix_max 
        AND m.prix >= :prix_min";

$params = [':prix_max' => $prix_max, ':prix_min' => $prix_min];

if($theme !== '') {
    $sql .= " AND t.libelle = :theme";
    $params[':theme'] = $theme;
}
if($regime !== '') {
    $sql .= " AND r.libelle = :regime";
    $params[':regime'] = $regime;
}
if($personnes > 0) {
    $sql .= " AND m.nb_personne_minimum >= :personnes";
    $params[':personnes'] = $personnes;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$menus = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nos Menus – Vite & Gourmand</title>
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
.main{max-width:1200px;margin:0 auto;padding:3rem 2rem;display:grid;grid-template-columns:280px 1fr;gap:2rem}
.filters{background:#fff;border:1px solid #f0e8d8;border-radius:12px;padding:1.5rem;height:fit-content;position:sticky;top:80px}
.filters h3{font-size:1rem;font-weight:700;margin-bottom:1.5rem;padding-bottom:.75rem;border-bottom:1px solid #f0e8d8}
.filter-group{margin-bottom:1.5rem}
.filter-group label{display:block;font-size:.82rem;font-weight:600;color:#888;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.75rem}
.filter-group input[type=range]{width:100%;accent-color:#f5a623}
.range-val{font-size:.85rem;color:#f5a623;font-weight:600;margin-top:.25rem}
.filter-group select{width:100%;padding:8px 10px;border:1px solid #e0d8cc;border-radius:6px;font-size:.9rem;background:#fffdf9}
.filter-group input[type=number]{width:100%;padding:8px 10px;border:1px solid #e0d8cc;border-radius:6px;font-size:.9rem;background:#fffdf9}
.price-range{display:flex;gap:.5rem;align-items:center;font-size:.85rem;color:#888}
.price-range input{width:80px}
.btn-reset{width:100%;padding:10px;background:transparent;border:1px solid #f0e8d8;border-radius:6px;font-size:.85rem;color:#888;cursor:pointer}
.btn-reset:hover{border-color:#f5a623;color:#f5a623}
.menus-section h2{font-size:1.1rem;color:#888;margin-bottom:1.5rem}
.menus-section h2 span{color:#2d2d2d;font-weight:700}
.menus-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.5rem}
.menu-card{background:#fff;border:1px solid #f0e8d8;border-radius:12px;overflow:hidden;transition:transform .2s,box-shadow .2s}
.menu-card:hover{transform:translateY(-4px);box-shadow:0 8px 30px rgba(245,166,35,.15)}
.menu-img{height:160px;display:flex;align-items:center;justify-content:center;font-size:3.5rem;background:linear-gradient(135deg,#f5a623,#e07510)}
.menu-body{padding:1.25rem}
.menu-badges{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.75rem}
.badge{font-size:.7rem;padding:3px 10px;border-radius:99px;font-weight:600}
.badge-theme{background:#fff3dc;color:#b07a00}
.badge-regime{background:#e8f5e9;color:#2e7d32}
.badge-stock{background:#e8f5e9;color:#2e7d32}
.badge-stock.complet{background:#fce4e4;color:#c62828}
.menu-title{font-size:1.05rem;font-weight:700;margin-bottom:.5rem}
.menu-desc{font-size:.85rem;color:#666;line-height:1.6;margin-bottom:1rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.menu-footer{display:flex;align-items:center;justify-content:space-between;padding-top:1rem;border-top:1px solid #f0e8d8}
.menu-info{font-size:.8rem;color:#888}
.menu-info strong{color:#2d2d2d;font-size:.95rem;display:block}
.btn-detail{background:#f5a623;color:#1a1a1a;padding:8px 16px;border-radius:6px;font-size:.82rem;font-weight:700;border:none;cursor:pointer}
.btn-detail:hover{background:#e09510}
.no-result{text-align:center;padding:3rem;color:#888}
footer{background:#1a1a1a;color:#ccc;padding:2rem;text-align:center;font-size:.85rem;margin-top:3rem}
footer a{color:#888;margin:0 .5rem}
footer a:hover{color:#f5a623}
@media(max-width:768px){.main{grid-template-columns:1fr}.filters{position:static}}
</style>
</head>
<body>

<nav>
  <a href="index.html" class="logo">Vite <span>& Gourmand</span></a>
  <div class="nav-links">
    <a href="menus.php">Nos menus</a>
    <a href="contact.html">Contact</a>
    <a href="login.php" class="btn-login">Connexion</a>
  </div>
</nav>

<div class="page-header">
  <h1>Nos <span>Menus</span></h1>
  <p>Découvrez notre sélection de menus pour tous vos événements</p>
</div>

<div class="main">
  <aside class="filters">
    <h3>🔍 Filtrer les menus</h3>
    <form method="GET" action="menus.php">
      <div class="filter-group">
        <label>Prix maximum</label>
        <input type="range" name="prix_max" min="50" max="500" value="<?= isset($_GET['prix_max'])?htmlspecialchars($_GET['prix_max']):500 ?>" oninput="this.nextElementSibling.querySelector('span').textContent=this.value">
        <div class="range-val">Jusqu'à <span><?= isset($_GET['prix_max'])?htmlspecialchars($_GET['prix_max']):500 ?></span> €</div>
      </div>
      <div class="filter-group">
        <label>Fourchette de prix</label>
        <div class="price-range">
          <input type="number" name="prix_min" placeholder="Min" value="<?= isset($_GET['prix_min'])?htmlspecialchars($_GET['prix_min']):'' ?>">
          <span>–</span>
          <input type="number" name="prix_fin" placeholder="Max" value="<?= isset($_GET['prix_fin'])?htmlspecialchars($_GET['prix_fin']):'' ?>">
        </div>
      </div>
      <div class="filter-group">
        <label>Thème</label>
        <select name="theme">
          <option value="">Tous les thèmes</option>
          <option value="Noël" <?= (isset($_GET['theme'])&&$_GET['theme']==='Noël')?'selected':'' ?>>Noël</option>
          <option value="Pâques" <?= (isset($_GET['theme'])&&$_GET['theme']==='Pâques')?'selected':'' ?>>Pâques</option>
          <option value="Classique" <?= (isset($_GET['theme'])&&$_GET['theme']==='Classique')?'selected':'' ?>>Classique</option>
          <option value="Événement" <?= (isset($_GET['theme'])&&$_GET['theme']==='Événement')?'selected':'' ?>>Événement</option>
        </select>
      </div>
      <div class="filter-group">
        <label>Régime</label>
        <select name="regime">
          <option value="">Tous les régimes</option>
          <option value="Classique" <?= (isset($_GET['regime'])&&$_GET['regime']==='Classique')?'selected':'' ?>>Classique</option>
          <option value="Végétarien" <?= (isset($_GET['regime'])&&$_GET['regime']==='Végétarien')?'selected':'' ?>>Végétarien</option>
          <option value="Vegan" <?= (isset($_GET['regime'])&&$_GET['regime']==='Vegan')?'selected':'' ?>>Vegan</option>
        </select>
      </div>
      <div class="filter-group">
        <label>Personnes minimum</label>
        <input type="number" name="personnes" placeholder="Ex: 10" value="<?= isset($_GET['personnes'])?htmlspecialchars($_GET['personnes']):'' ?>">
      </div>
      <button type="submit" class="btn-reset" style="background:#f5a623;color:#1a1a1a;font-weight:700;border-color:#f5a623;margin-bottom:.5rem">Filtrer</button>
      <a href="menus.php"><button type="button" class="btn-reset">Réinitialiser</button></a>
    </form>
  </aside>

  <div class="menus-section">
    <h2>Affichage : <span><?= count($menus) ?></span> menu(s)</h2>
    <div class="menus-grid">
      <?php if(count($menus) === 0): ?>
        <div class="no-result"><p>Aucun menu ne correspond à vos critères.</p></div>
      <?php else: ?>
        <?php foreach($menus as $menu): ?>
          <?php
            $emojis = [1=>'🎄',2=>'🐣',3=>'💍',4=>'🥗',5=>'🌱',6=>'🏢'];
            $emoji = $emojis[$menu['menu_id']] ?? '🍽️';
          ?>
          <div class="menu-card">
            <div class="menu-img"><?= $emoji ?></div>
            <div class="menu-body">
              <div class="menu-badges">
                <span class="badge badge-theme"><?= htmlspecialchars($menu['theme']) ?></span>
                <span class="badge badge-regime"><?= htmlspecialchars($menu['regime']) ?></span>
                <span class="badge badge-stock <?= $menu['quantite_restante']==0?'complet':'' ?>">
                  <?= $menu['quantite_restante']>0 ? $menu['quantite_restante'].' restant(s)' : 'Complet' ?>
                </span>
              </div>
              <div class="menu-title"><?= htmlspecialchars($menu['titre']) ?></div>
              <div class="menu-desc"><?= htmlspecialchars($menu['description']) ?></div>
              <div class="menu-footer">
                <div class="menu-info">
                  <strong><?= $menu['prix'] ?> €</strong>
                  pour <?= $menu['nb_personne_minimum'] ?> pers. min.
                </div>
                <button class="btn-detail" onclick="window.location.href='detail-menu.php?id=<?= $menu['menu_id'] ?>'">Voir le détail</button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<footer>
  <span>© 2024 Vite & Gourmand</span>
  <a href="mentions-legales.html">Mentions légales</a>
  <a href="cgv.html">CGV</a>
</footer>

</body>
</html>