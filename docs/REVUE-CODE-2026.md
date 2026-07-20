# Revue du site WordPress (thème Bellevue) — Juillet 2026

Site hébergé sur OVH · Thème **Bellevue `bellevuex` v4.2.18** (Themovation) ·
Extensions clés : MotoPress Hotel Booking, WooCommerce, Elementor, Kirki.

Cette revue couvre les trois axes demandés : **Sécurité**, **Expérience
utilisateur / Performance**, et **Qualité du code / Architecture**.

---

## 0. Le constat le plus important

Le dépôt contient **le thème premium Bellevue quasiment vierge** + un
`.htaccess` minimal. Bellevue est du **code tiers** : ce n'est ni utile ni
souhaitable d'en réécrire les entrailles, car **chaque mise à jour du thème
écrase tous ses fichiers**. Votre levier d'amélioration n'est donc pas
« corriger le thème », mais :

1. **Ne jamais modifier le thème parent** → passer par un **thème enfant**
   (créé dans cette revue : `wp-content/themes/bellevuex-child/`).
2. **Rester à jour** (thème, extensions, WordPress core, PHP) — c'est le
   facteur n°1 de sécurité sur WordPress.
3. **Optimiser la couche serveur** (`.htaccess`) et le **chargement des assets**
   pour la performance et l'UX.

---

## 1. 🔴 Sécurité (priorité haute)

### 1.1 Maintenir le thème à jour — critique
Le thème a un **historique de vulnérabilités** : une faille *Broken Access
Control* affectait Bellevue **≤ 4.2.2** (source Patchstack). Vous êtes en
**4.2.18**, donc au-dessus de cette version — cette faille précise est corrigée.
Mais cela prouve la règle : **un thème premium non mis à jour finit par être
vulnérable.**
- ✅ **Action** : vérifier dans l'admin (Apparence → Thèmes) que 4.2.18 est bien
  la dernière version ; sinon mettre à jour. Idem pour l'extension **Aloha
  PowerPack** (liée au thème) et **toutes** les extensions.
- ✅ **Action** : activer/planifier des mises à jour régulières et surveiller
  [la page Patchstack du thème](https://patchstack.com/database/wordpress/theme/bellevuex).

### 1.2 Le thème est exécuté « en direct », sans thème enfant
Sans thème enfant, toute retouche se fait dans le parent et **bloque de fait les
mises à jour** (on ne met plus à jour pour ne pas perdre ses changements) → dette
de sécurité qui s'accumule.
- ✅ **Corrigé dans cette revue** : thème enfant `bellevuex-child/` prêt à activer
  (voir son `README.md`).

### 1.3 Ressource chargée en HTTP + dépendance obsolète
`wp-content/themes/bellevuex/functions.php:322` charge une CSS jQuery UI **1.8.2
(2010)** depuis `http://ajax.googleapis.com` **en clair (HTTP)**.
- Impact : avertissement **« contenu mixte »** sur un site HTTPS, fuite de
  navigation vers Google, dépendance vieille de 15 ans.
- Portée : côté **admin uniquement**, et seulement tant qu'Aloha n'est pas
  installé → risque réel faible, mais à signaler.
- ✅ **Action** : à remonter à l'éditeur du thème (code parent, ne pas patcher en
  direct). Contournement : garder Aloha PowerPack installé (le bloc ne se
  déclenche plus).

### 1.4 `unserialize()` sur des données `$_POST`
`lib/thmv_registration_setup.php:510` :
`unserialize(stripslashes(stripslashes($_POST['plugins'])))`.
Désérialiser une entrée utilisateur est un motif à risque (**PHP Object
Injection**).
- Portée : flux d'**activation/enregistrement du thème en admin** → exploitation
  très limitée, mais mauvais motif.
- ✅ **Action** : code parent → à signaler à l'éditeur via le programme Patchstack
  du thème ; ne pas modifier en direct.

### 1.5 Durcissement `.htaccess` (voir §2.1)
Votre `.htaccess` actuel ne protège pas `wp-config.php`, n'interdit pas le
listing de répertoires, ni l'exécution de PHP dans `uploads/`, et n'envoie aucun
en-tête de sécurité. → **Proposition fournie** : `docs/htaccess-recommande.txt`.

### 1.6 Bonnes pratiques `wp-config.php` (à vérifier — fichier non versionné, à raison)
- `define('DISALLOW_FILE_EDIT', true);` → désactive l'éditeur de thème/extension
  dans l'admin (empêche un attaquant ayant un accès admin d'injecter du code).
- Clés/salts uniques (régénérables via https://api.wordpress.org/secret-key/1.1/salt/).
- `define('WP_DEBUG', false);` en production.
- Préfixe de table différent de `wp_` si possible.

---

## 2. 🟠 Performance & Expérience utilisateur (priorité haute — impact direct visiteurs)

### 2.1 `.htaccess` sans compression ni cache — le gain le plus rentable
Fichier actuel = strict minimum WordPress. Aucun **gzip**, aucun **cache
navigateur**, aucun en-tête. Ce sont les optimisations au meilleur rapport
effort/gain pour vos **Core Web Vitals**.
- ✅ **Fourni** : `docs/htaccess-recommande.txt` (gzip + expires + sécurité +
  protection fichiers), avec mode d'emploi prudent.

### 2.2 Beaucoup de fichiers CSS séparés (rendu bloquant)
`lib/scripts.php` charge, selon les extensions actives : `base.css`, `app.css`,
`hotel-booking.css`, `header.css`, `headhesive.css`, `preloader.css`,
`forms.css`, `woocommerce.css`, `booked-calendar.css`, `groovy-menu.css`…
Chaque `<link>` en `<head>` est **bloquant pour le rendu**.
- 👍 Bon point : chargement **conditionnel** et **cache-busting** via
  `filemtime()` (déjà bien fait).
- ✅ **Action** (sans toucher au thème) : installer un plugin de cache/optimisation
  — **WP Rocket** (payant) ou **LiteSpeed Cache / W3 Total Cache / FlyingPress**.
  Activer : minification + **combinaison CSS**, *defer* du JS, CSS critique.
  Gain typique majeur sur mobile.

### 2.3 Preloader (écran de chargement)
`preloader.css` est chargé si l'option `themo_preloader` est active (par défaut
`true`). Un preloader **retarde artificiellement** l'affichage et **dégrade le
LCP** (métrique Core Web Vitals) et l'UX perçue.
- ✅ **Action** : Customizer → désactiver le preloader, sauf besoin esthétique fort.

### 2.4 Polyfills anciens chargés inutilement
`assets/js/vendor/` embarque `html5shiv.min.js`, `respond.min.js`, `retina.min.js`
— des polyfills pour **Internet Explorer 8/9** (obsolètes, 0 % de trafic
aujourd'hui). Poids mort.
- ✅ **Action** : code parent → laisser tel quel (à l'éditeur), mais s'assurer que
  le plugin d'optimisation les défère ; ne pas s'en préoccuper en priorité.

### 2.5 jQuery en tête de page
`lib/scripts.php:10` force `wp_enqueue_script('jquery')`. Beaucoup de thèmes/plugins
en dépendent, mais jQuery en `<head>` est bloquant.
- ✅ **Action** : laisser le plugin d'optimisation gérer le *defer* ; ne pas forcer
  à la main dans le parent.

### 2.6 Images
Le thème embarque des PNG non optimisés dans `assets/images/`. Surtout, vos
**images de contenu** (chambres, galeries) sont le plus gros poste de poids sur un
site d'hôtel.
- ✅ **Action** : servir en **WebP/AVIF** + **lazy-loading** (natif depuis WP 5.5,
  renforcé par le plugin d'optimisation) + dimensionner correctement.

### 2.7 Accessibilité (UX inclusive + SEO)
Le thème se déclare `accessibility-ready`. À vérifier sur le site réel :
- `alt` renseignés sur les images de contenu (surtout chambres).
- Contrastes de texte suffisants (WCAG AA).
- Navigation clavier du menu et du **tunnel de réservation** MotoPress.
- Hiérarchie des titres `h1→h2→h3` cohérente.
- ✅ **Action** : audit rapide avec Lighthouse (onglet *Accessibility*) et l'extension
  *axe DevTools*.

---

## 3. 🟡 Qualité du code & Workflow (priorité moyenne)

### 3.1 Versionner proprement (et pas le site entier)
- ✅ **Corrigé** : le garde-fou s'appelait `.gitignore.txt` (inactif car mauvaise
  extension). Renommé en **`.gitignore`** et enrichi (secrets, dumps, logs,
  fichiers Duplicator, artefacts d'éditeur).
- 💡 **Recommandation** : à terme, ne versionner **que votre thème enfant et votre
  code maison**, pas le thème parent tiers (qui se réinstalle via l'admin). Cela
  clarifie ce qui est *à vous* vs *à l'éditeur*.

### 3.2 Ne jamais modifier le thème parent
Point déjà couvert (§1.2). Tout override → `bellevuex-child/` (CSS, `functions.php`,
ou copie de template en conservant le chemin relatif).

### 3.3 Petit point i18n
`functions.php:291` passe une **constante** à `__()` :
`__(THMV_WIDGET_PACK_ACTIVATION_ERROR_NOTICE, 'bellevue')`. Les outils
d'extraction de traductions ne détectent pas les chaînes dynamiques → cette
chaîne ne sera pas traduisible. Détail, code parent → à signaler à l'éditeur.

### 3.4 Restes de migration Duplicator
Le `.htaccess` mentionne un dossier `original_files_` et un import Duplicator du
18/10/2025. Vérifiez qu'il ne reste pas sur le serveur, à la racine web, de
**fichiers d'installation Duplicator** (`installer.php`, `dup-installer/`,
archives `.zip`/`.sql`) : ce sont des **vecteurs d'attaque classiques** s'ils
traînent en production.
- ✅ **Action** : supprimer tout reste d'installation Duplicator du serveur.
  (Ajouté au `.gitignore` pour ne jamais les committer.)

### 3.5 Environnement de préproduction
Vous n'avez pas de filet pour tester (activation du thème enfant, mises à jour,
plugin de cache) sans risque sur le site live.
- ✅ **Action** : OVH permet de cloner un hébergement / créer un sous-domaine de
  préproduction. Tester d'abord là-bas.

---

## 4. Plan d'action priorisé

| # | Action | Axe | Effort | Où |
|---|--------|-----|--------|-----|
| 1 | Vérifier & appliquer les mises à jour (WP core, thème, **toutes** extensions, PHP 8.2+) | Sécurité | Faible | Admin |
| 2 | Supprimer les restes d'installation Duplicator du serveur | Sécurité | Faible | Serveur |
| 3 | Appliquer `docs/htaccess-recommande.txt` (compression + cache + sécurité) | Perf/Sécu | Faible | `.htaccess` |
| 4 | Activer un plugin de cache/optimisation (minif, defer, WebP, lazy-load) | Perf/UX | Faible | Admin |
| 5 | Désactiver le preloader | UX | Très faible | Customizer |
| 6 | Vérifier `wp-config.php` (DISALLOW_FILE_EDIT, WP_DEBUG=false, salts) | Sécurité | Faible | Serveur |
| 7 | Activer le **thème enfant** `bellevuex-child` et y migrer toute personnalisation | Qualité | Moyen | Admin + FTP |
| 8 | Audit Lighthouse (Perf + Accessibilité) et corriger les alertes | UX | Moyen | — |
| 9 | Mettre en place une **préproduction** OVH | Workflow | Moyen | OVH |
| 10 | Signaler à l'éditeur les points code parent (§1.3, §1.4, §3.3) | Sécurité | Faible | Patchstack |

---

## 5. Ce qui a été livré dans cette revue

- `wp-content/themes/bellevuex-child/` — thème enfant prêt à activer (`style.css`,
  `functions.php`, `README.md`).
- `docs/htaccess-recommande.txt` — `.htaccess` durci, avec mode d'emploi prudent.
- `.gitignore` — corrigé (l'ancien `.gitignore.txt` était inactif) et enrichi.
- `docs/REVUE-CODE-2026.md` — ce rapport.

> **Aucun fichier du thème `bellevuex` ni le `.htaccess` de production n'a été
> modifié** : toutes les améliorations sont soit additives (thème enfant), soit
> des propositions documentées à appliquer vous-même après sauvegarde et test.
