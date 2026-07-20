# 🚨 Incident de sécurité — Backdoor détectée (Juillet 2026)

**Gravité : CRITIQUE — site compromis.**
Une porte dérobée d'exécution de code à distance a été trouvée dans le paquet
`bellevuex-child/customizer-polylang-master/`, faussement présenté comme
l'extension « Customizer for Polylang ».

---

## 1. Ce qui a été trouvé (indicateurs de compromission — IoC)

**Fichier malveillant principal :**
`wp-content/themes/bellevuex-child/customizer-polylang-master/lock360.php`
→ Télécharge et exécute du code PHP arbitraire depuis des serveurs de commande
et contrôle (C2), puis efface la trace.

**Serveurs C2 (à bloquer / rechercher dans les logs) :**
- `https://c.icw2.xyz/`
- `https://c2.icw7.com/`

**Fichiers `.htaccess` malveillants** (autorisent l'accès direct aux backdoors) :
- `bellevuex-child/.htaccess`
- `bellevuex-child/assets/.htaccess`
- `bellevuex-child/js/.htaccess`
- `bellevuex-child/customizer-polylang-master/.htaccess`
- `bellevuex-child/customizer-polylang-master/.github/.htaccess`

**Noms de fichiers malveillants référencés (à rechercher sur TOUT le serveur) :**
`lock360.php`, `wp-l0gin.php`, `wp-the1me.php`, `wp-scr1pts.php`, `radio.php`,
`about.php`, et tout `admin.php` / `content.php` / `index.php` inhabituel hors
de leur emplacement normal. Chercher aussi un fichier caché `.c`.

> ⚠️ Cette backdoor s'exécute par simple accès URL, **indépendamment du thème
> actif**. La supprimer du thème ne suffit pas : il faut nettoyer tout le serveur.

---

## 2. Actions immédiates (contenir) — dans l'heure

1. **Mettre le site en maintenance** (ou le couper temporairement) via l'Espace
   Client OVH, pour stopper l'exploitation pendant le nettoyage.
2. **Changer TOUS les mots de passe** (en supposant qu'ils sont tous connus de l'attaquant) :
   - comptes administrateurs WordPress,
   - accès **OVH** (Espace Client),
   - **FTP/SFTP/SSH**,
   - **base de données** (puis reporter le nouveau mot de passe dans `wp-config.php`).
3. **Régénérer les clés de sécurité** (salts) dans `wp-config.php` via
   https://api.wordpress.org/secret-key/1.1/salt/ → déconnecte toutes les sessions,
   y compris celles de l'attaquant.
4. **Vérifier les comptes administrateurs** : supprimer tout utilisateur admin
   inconnu (Utilisateurs → Administrateurs).

---

## 3. Nettoyage du serveur (SSH OVH)

Se connecter en SSH et **localiser** les fichiers infectés (ne rien supprimer
avant d'avoir la liste complète) :

```bash
cd ~/www   # racine WordPress chez OVH

# 1) La backdoor et ses noms connus
find . -type f \( -name "lock360.php" -o -name "wp-l0gin.php" \
  -o -name "wp-the1me.php" -o -name "wp-scr1pts.php" -o -name "radio.php" \) -print

# 2) Références aux serveurs C2 (le plus fiable)
grep -rEl "icw2\.xyz|icw7\.com|lock360" . 2>/dev/null

# 3) .htaccess qui « autorisent » des fichiers PHP suspects
grep -rEl "wp-l0gin|wp-the1me|wp-scr1pts|lock360" --include=".htaccess" . 2>/dev/null

# 4) Motifs d'exécution distante génériques (à examiner un par un)
grep -rEl "eval\(|file_get_contents\(\\\$url|curl_exec|@require\(\\\$" . 2>/dev/null \
  | grep -v "plugin-update-checker"

# 5) Fichiers récemment modifiés (adapter la date à celle de l'infection)
find . -type f -name "*.php" -newermt "2025-10-01" -printf "%TY-%Tm-%Td %p\n" | sort
```

**Puis** : supprimer les fichiers confirmés malveillants et les `.htaccess`
malveillants listés au §1. Vérifier qu'il ne reste **aucun** `.htaccess`
whitelistant des noms de fichiers PHP.

---

## 4. Remise en état (recommandé : repartir propre)

La suppression manuelle ne garantit **jamais** à 100 % qu'il ne reste pas une
autre backdoor. Ordre de préférence :

1. **Restaurer une sauvegarde saine** antérieure à l'infection (OVH conserve des
   sauvegardes ; vérifier la date — l'import Duplicator date du 18/10/2025).
2. Sinon, **reconstruire proprement** :
   - Réinstaller le **cœur WordPress** depuis l'admin (Outils → Santé, ou
     « Réinstaller la version ») ou par un fichier officiel.
   - **Supprimer et réinstaller** le thème Bellevue et **toutes** les extensions
     depuis leurs **sources officielles** (jamais de version « nulled »).
   - Ne conserver de l'ancien site que le contenu vérifié (`uploads/` scanné, base
     de données inspectée).
3. **Scanner** avec un outil dédié : extension **Wordfence** ou **Sucuri
   SiteCheck**, ou le service de nettoyage OVH / un prestataire.
4. Vérifier les **tâches planifiées** (`wp cron event list` avec WP-CLI) et la
   table `wp_options` (entrées d'auto-chargement suspectes).

---

## 5. Prévention (pour ne pas recommencer)

- ❌ **Ne JAMAIS installer de thème/extension « nulled » (piraté)**. C'est LE
  vecteur d'infection ici. Les versions gratuites piratées embarquent presque
  toujours une backdoor.
- ✅ « Customizer for Polylang » est **gratuit et open-source** : le récupérer
  uniquement depuis la source officielle
  (https://github.com/soderlind/customizer-polylang ou le dépôt WordPress.org),
  et l'installer **comme extension**, pas dans un thème.
- ✅ Empêcher l'exécution de PHP dans les dossiers d'upload/thèmes writables
  (voir `docs/htaccess-recommande.txt`).
- ✅ Installer un pare-feu applicatif (Wordfence / Patchstack) et surveiller.
- ✅ Sauvegardes automatiques régulières + hors-site.
- ✅ Tenir WordPress, thème et extensions à jour.

---

## 6. Statut côté dépôt Git

- Sur la branche `main`, le commit « mise à jour bellevuex » (`1777c99`) a
  introduit ces fichiers malveillants. Ils sont **circonscrits à
  `wp-content/themes/bellevuex-child/`**.
- La branche `claude/wordpress-ovh-review-29tuya` fournit une version **saine**
  du thème enfant (sans `lock360.php`, sans `.htaccess` malveillants, sans le
  dossier `customizer-polylang-master`).
- **Le vrai risque est sur le serveur OVH, pas dans le dépôt.** Nettoyer le dépôt
  ne nettoie pas le site en ligne.
