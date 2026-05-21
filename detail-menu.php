<?php
session_start();
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Récupérer le menu
$stmt = $pdo->prepare("SELECT m.*, t.libelle as theme, r.libelle as regime 
                        FROM menu m 
                        JOIN theme t ON m.theme_id = t.theme_id 
                        JOIN regime r ON m.regime_id = r.regime_id 
                        WHERE m.menu_id = ?");
$stmt->execute([$id]);
$menu = $stmt->fetch();

if(!$menu) {
    header('Location: menus.php');
    exit;
}

// Récupérer les plats du menu
$stmt2 = $pdo->prepare("SELECT p.*, GROUP_CONCAT(a.libelle SEPARATOR ', ') as allergenes 
                         FROM plat p 
                         LEFT JOIN plat_allergene pa ON p.plat_id = pa.plat_id 
                         LEFT JOIN allergene a ON pa.allergene_id = a.allergene_id 
                         WHERE p.plat_id IN (SELECT plat_id FROM menu_plat WHERE menu_id = ?)
                         GROUP BY p.plat_id");
$stmt2->execute([$id]);
$plats = $stmt2->fetchAll();

$emojis = [1=>'🎄',2=>'🐣',3=>'💍',4=>'🥗',5=>'🌱',6=>'🏢'];
$emoji = $emojis[$menu['menu_id']] ?? '🍽️';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($menu['titre']) ?> – Vite & Gourmand</title>
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
.container{max-width:1100px;margin:0 auto;padding:3rem 2rem}
.back{display:inline-flex;align-items:center;gap:.5rem;color:#888;font-size:.9rem;margin-bottom:2rem;transition:color .2s}
.back:hover{color:#f5a623}
.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:3rem;margin-bottom:3rem}
.detail-img{background:linear-gradient(135deg,#f5a623,#e07510);border-radius:16px;height:350px;display:flex;align-items:center;justify-content:center;font-size:6rem}
.detail-info h1{font-size:2rem;font-weight:800;margin-bottom:1rem;line-height:1.3}
.badges{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem}
.badge{font-size:.75rem;padding:4px 12px;border-radius:99px;font-weight:600}
.badge-theme{background:#fff3dc;color:#b07a00}
.badge-regime{background:#e8f5e9;color:#2e7d32}
.badge-stock{background:#e8f5e9;color:#2e7d32}
.badge-stock.complet{background:#fce4e4;color:#c62828}
.desc{color:#555;line-height:1.8;margin-bottom:1.5rem;font-size:.95rem}
.price-box{background:#fff;border:2px solid #f5a623;border-radius:12px;padding:1.5rem;margin-bottom:1.5rem}
.price-box .price{font-size:2.5rem;font-weight:800;color:#f5a623}
.price-box .price-detail{font-size:.85rem;color:#888;margin-top:.25rem}
.price-box .reduction{font-size:.82rem;color:#2e7d32;background:#e8f5e9;padding:6px 12px;border-radius:6px;margin-top:.75rem;display:inline-block}
.conditions{background:#fff8e1;border-left:4px solid #f5a623;border-radius:0 8px 8px 0;padding:1.25rem;margin-bottom:1.5rem}
.conditions h4{font-size:.9rem;font-weight:700;color:#b07a00;margin-bottom:.5rem}
.conditions p{font-size:.85rem;color:#666;line-height:1.7}
.btn-commander{width:100%;padding:16px;background:#f5a623;color:#1a1a1a;border:none;border-radius:8px;font-size:1rem;font-weight:700;cursor:pointer;transition:background .2s}
.btn-commander:hover{background:#e09510}
.btn-commander.disabled{background:#ccc;cursor:not-allowed}
.section-card{background:#fff;border:1px solid #f0e8d8;border-radius:12px;padding:1.5rem;margin-bottom:1.5rem}
.section-card h3{font-size:1rem;font-weight:700;margin-bottom:1.25rem;padding-bottom:.75rem;border-bottom:1px solid #f0e8d8}
.plat-item{padding:.75rem 0;border-bottom:1px solid #f5f0e8}
.plat-item:last-child{border-bottom:none}
.plat-type{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#f5a623;margin-bottom:.25rem}
.plat-nom{font-size:.95rem;font-weight:600;margin-bottom:.25rem}
.plat-allergenes{font-size:.78rem;color:#c62828;background:#fce4e4;padding:3px 8px;border-radius:4px;display:inline-block;margin-top:.25rem}
.plat-allergenes.none{color:#2e7d32;background:#e8f5e9}
.no-plats{color:#888;font-size:.9rem;font-style:italic}
footer{background:#1a1a1a;color:#ccc;padding:2rem;text-align:center;font-size:.85rem;margin-top:3rem}
footer a{color:#888;margin:0 .5rem}
footer a:hover{color:#f5a623}
@media(max-width:768px){.detail-grid{grid-template-columns:1fr}}
</style>
</head>
<body>

<nav>
  <a href="index.html" class="logo">Vite <span>& Gourmand</span></a>
  <div class="nav-links">
    <a href="menus.php">Nos menus</a>
    <a href="contact.html">Contact</a>
    <?php if(isset($_SESSION['user_id'])): ?>
      <a href="logout.php" class="btn-login">Déconnexion</a>
    <?php else: ?>
      <a href="login.php" class="btn-login">Connexion</a>
    <?php endif; ?>
  </div>
</nav>

<div class="container">
  <a href="menus.php" class="back">← Retour aux menus</a>

  <div class="detail-grid">
    <div class="detail-img"><?= $emoji ?></div>
    <div class="detail-info">
      <h1><?= htmlspecialchars($menu['titre']) ?></h1>
      <div class="badges">
        <span class="badge badge-theme"><?= htmlspecialchars($menu['theme']) ?></span>
        <span class="badge badge-regime"><?= htmlspecialchars($menu['regime']) ?></span>
        <span class="badge badge-stock <?= $menu['quantite_restante']==0?'complet':'' ?>">
          <?= $menu['quantite_restante']>0 ? $menu['quantite_restante'].' place(s) restante(s)' : 'Complet' ?>
        </span>
      </div>
      <p class="desc"><?= htmlspecialchars($menu['description']) ?></p>
      <div class="price-box">
        <div class="price"><?= $menu['prix'] ?> €</div>
        <div class="price-detail">pour <?= $menu['nb_personne_minimum'] ?> personnes minimum</div>
        <div class="reduction">🎉 −10% à partir de <?= $menu['nb_personne_minimum']+5 ?> personnes</div>
      </div>
      <div class="conditions">
        <h4>⚠️ Conditions importantes</h4>
        <p><?= htmlspecialchars($menu['conditions']) ?></p>
      </div>
      <?php if($menu['quantite_restante'] > 0): ?>
        <?php if(isset($_SESSION['user_id'])): ?>
          <button class="btn-commander" onclick="window.location.href='commande.php?id=<?= $menu['menu_id'] ?>'">Commander ce menu</button>
        <?php else: ?>
          <button class="btn-commander" onclick="window.location.href='login.php?redirect=commande.php?id=<?= $menu['menu_id'] ?>'">Se connecter pour commander</button>
        <?php endif; ?>
      <?php else: ?>
        <button class="btn-commander disabled" disabled>Menu indisponible</button>
      <?php endif; ?>
    </div>
  </div>

  <div class="section-card">
    <h3>🍽️ Composition du menu</h3>
    <?php if(count($plats) > 0): ?>
      <?php foreach($plats as $plat): ?>
        <div class="plat-item">
          <div class="plat-type"><?= htmlspecialchars($plat['type_plat']) ?></div>
          <div class="plat-nom"><?= htmlspecialchars($plat['titre']) ?></div>
          <?php if($plat['allergenes']): ?>
            <span class="plat-allergenes">⚠️ Allergènes : <?= htmlspecialchars($plat['allergenes']) ?></span>
          <?php else: ?>
            <span class="plat-allergenes none">✓ Sans allergènes majeurs</span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="no-plats">Composition détaillée disponible sur demande.</p>
    <?php endif; ?>
  </div>

</div>

<footer>
  <span>© 2024 Vite & Gourmand</span>
  <a href="mentions-legales.html">Mentions légales</a>
  <a href="cgv.html">CGV</a>
</footer>

</body>
</html>