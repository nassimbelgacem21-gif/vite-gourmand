<?php
session_start();
require_once 'config.php';

$erreur = '';
$succes = '';

// CONNEXION
if(isset($_POST['action']) && $_POST['action'] === 'connexion') {
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];

    if(empty($email) || empty($password)) {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        $stmt = $pdo->prepare("SELECT u.*, r.libelle as role FROM utilisateur u JOIN role r ON u.role_id = r.role_id WHERE u.email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['utilisateur_id'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            header('Location: index.html');
            exit;
        } else {
            $erreur = 'Email ou mot de passe incorrect.';
        }
    }
}

// INSCRIPTION
if(isset($_POST['action']) && $_POST['action'] === 'inscription') {
    $nom = htmlspecialchars(trim($_POST['nom']));
    $prenom = htmlspecialchars(trim($_POST['prenom']));
    $email = htmlspecialchars(trim($_POST['email']));
    $telephone = htmlspecialchars(trim($_POST['telephone']));
    $adresse = htmlspecialchars(trim($_POST['adresse']));
    $password = $_POST['password'];

    if(empty($nom)||empty($prenom)||empty($email)||empty($telephone)||empty($adresse)||empty($password)) {
        $erreur = 'Veuillez remplir tous les champs.';
    } elseif(!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{10,}$/', $password)) {
        $erreur = 'Le mot de passe doit contenir 10 caractères minimum avec majuscule, minuscule, chiffre et caractère spécial.';
    } else {
        // Vérifier si email déjà utilisé
        $stmt = $pdo->prepare("SELECT utilisateur_id FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        if($stmt->fetch()) {
            $erreur = 'Cet email est déjà utilisé.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, password, telephone, adresse_postale, role_id) VALUES (?, ?, ?, ?, ?, ?, 2)");
            $stmt->execute([$nom, $prenom, $email, $hash, $telephone, $adresse]);
            $succes = 'Compte créé avec succès ! Vous pouvez vous connecter.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion – Vite & Gourmand</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',sans-serif;color:#2d2d2d;background:#fffdf9;min-height:100vh}
a{text-decoration:none;color:inherit}
nav{background:#1a1a1a;padding:0 2rem;display:flex;align-items:center;justify-content:space-between;height:64px;position:sticky;top:0;z-index:100}
.logo{color:#f5a623;font-size:1.4rem;font-weight:700}
.logo span{color:#fff}
.nav-links{display:flex;gap:2rem;align-items:center}
.nav-links a{color:#ccc;font-size:.9rem;transition:color .2s}
.nav-links a:hover{color:#f5a623}
.auth-wrapper{display:flex;align-items:center;justify-content:center;padding:4rem 2rem;min-height:calc(100vh - 64px)}
.auth-box{background:#fff;border:1px solid #f0e8d8;border-radius:16px;padding:2.5rem;width:100%;max-width:440px}
.auth-box h1{font-size:1.6rem;font-weight:800;margin-bottom:.5rem}
.auth-box h1 span{color:#f5a623}
.auth-box p{color:#888;font-size:.9rem;margin-bottom:2rem}
.tabs{display:flex;background:#f5f0e8;border-radius:8px;padding:4px;margin-bottom:2rem}
.tab{flex:1;padding:10px;text-align:center;font-size:.9rem;font-weight:600;border-radius:6px;cursor:pointer;transition:all .2s;color:#888;border:none;background:transparent}
.tab.active{background:#fff;color:#2d2d2d;box-shadow:0 1px 4px rgba(0,0,0,.1)}
.form-group{margin-bottom:1.25rem}
.form-group label{display:block;font-size:.82rem;font-weight:600;color:#555;margin-bottom:.5rem}
.form-group input{width:100%;padding:12px 14px;border:1px solid #e0d8cc;border-radius:8px;font-size:.95rem;background:#fffdf9;transition:border-color .2s;font-family:inherit}
.form-group input:focus{outline:none;border-color:#f5a623}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.btn-submit{width:100%;padding:14px;background:#f5a623;color:#1a1a1a;border:none;border-radius:8px;font-size:1rem;font-weight:700;cursor:pointer;font-family:inherit;margin-top:.5rem}
.btn-submit:hover{background:#e09510}
.alert{padding:12px 14px;border-radius:8px;font-size:.85rem;margin-bottom:1.25rem}
.alert.success{background:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9}
.alert.error{background:#fce4e4;color:#c62828;border:1px solid #ffcdd2}
.password-hint{font-size:.75rem;color:#999;margin-top:.4rem}
footer{background:#1a1a1a;color:#ccc;padding:2rem;text-align:center;font-size:.85rem}
footer a{color:#888;margin:0 .5rem}
footer a:hover{color:#f5a623}
</style>
</head>
<body>

<nav>
  <a href="index.html" class="logo">Vite <span>& Gourmand</span></a>
  <div class="nav-links">
    <a href="menus.php">Nos menus</a>
    <a href="contact.html">Contact</a>
  </div>
</nav>

<div class="auth-wrapper">
  <div class="auth-box">
    <div class="tabs">
      <button class="tab <?= !isset($_GET['tab'])||$_GET['tab']==='connexion'?'active':'' ?>" onclick="showTab('connexion')">Connexion</button>
      <button class="tab <?= isset($_GET['tab'])&&$_GET['tab']==='inscription'?'active':'' ?>" onclick="showTab('inscription')">Inscription</button>
    </div>

    <?php if($erreur): ?>
      <div class="alert error"><?= $erreur ?></div>
    <?php endif; ?>
    <?php if($succes): ?>
      <div class="alert success"><?= $succes ?></div>
    <?php endif; ?>

    <!-- CONNEXION -->
    <div id="tab-connexion" <?= isset($_GET['tab'])&&$_GET['tab']==='inscription'?'style="display:none"':'' ?>>
      <h1>Bon retour <span>!</span></h1>
      <p>Connectez-vous à votre compte</p>
      <form method="POST" action="login.php">
        <input type="hidden" name="action" value="connexion">
        <div class="form-group">
          <label>Adresse email</label>
          <input type="email" name="email" placeholder="votre@email.com" required>
        </div>
        <div class="form-group">
          <label>Mot de passe</label>
          <input type="password" name="password" placeholder="••••••••••" required>
        </div>
        <button type="submit" class="btn-submit">Se connecter</button>
      </form>
    </div>

    <!-- INSCRIPTION -->
    <div id="tab-inscription" <?= !isset($_GET['tab'])||$_GET['tab']==='connexion'?'style="display:none"':'' ?>>
      <h1>Créer un <span>compte</span></h1>
      <p>Rejoignez Vite & Gourmand</p>
      <form method="POST" action="login.php?tab=inscription">
        <input type="hidden" name="action" value="inscription">
        <div class="form-row">
          <div class="form-group">
            <label>Prénom</label>
            <input type="text" name="prenom" placeholder="Julie" required>
          </div>
          <div class="form-group">
            <label>Nom</label>
            <input type="text" name="nom" placeholder="Dupont" required>
          </div>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" placeholder="votre@email.com" required>
        </div>
        <div class="form-group">
          <label>Téléphone</label>
          <input type="tel" name="telephone" placeholder="06 12 34 56 78" required>
        </div>
        <div class="form-group">
          <label>Adresse postale</label>
          <input type="text" name="adresse" placeholder="12 rue des Fleurs, Bordeaux" required>
        </div>
        <div class="form-group">
          <label>Mot de passe</label>
          <input type="password" name="password" placeholder="••••••••••" required>
          <div class="password-hint">10 caractères min. avec majuscule, minuscule, chiffre et caractère spécial</div>
        </div>
        <button type="submit" class="btn-submit">Créer mon compte</button>
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
  document.getElementById('tab-connexion').style.display = tab==='connexion'?'block':'none';
  document.getElementById('tab-inscription').style.display = tab==='inscription'?'block':'none';
  document.querySelectorAll('.tab').forEach((t,i)=>t.classList.toggle('active',(tab==='connexion'&&i===0)||(tab==='inscription'&&i===1)));
}
</script>
</body>
</html>