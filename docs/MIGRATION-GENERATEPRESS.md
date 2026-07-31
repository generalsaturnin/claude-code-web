# Migration Bellevue → GeneratePress (sans Elementor)

Site : `la-crochardiere.fr` · Cible : **GeneratePress + GenerateBlocks**, contenu
en **éditeur de blocs natif**. Objectif : site vitrine léger, low-maintenance,
sans page builder ni machinerie hôtelière.

---

## 0. ⚠️ AVANT TOUT — sauvegarde complète (point de restauration propre)

Vous n'aviez aucune sauvegarde saine. Maintenant que le site est nettoyé, **on
fige une sauvegarde AVANT de toucher à quoi que ce soit**. En SSH :

```bash
cd ~/la-crochardiere.fr
# Base de données
php wp-cli.phar db export ~/backup-avant-migration-$(date +%F).sql
# Fichiers (hors caches lourds)
tar czf ~/backup-files-$(date +%F).tar.gz wp-content wp-config.php .htaccess
ls -lh ~/backup-*
```
Téléchargez ces 2 fichiers **en local** (WinSCP) avant de continuer.

---

## 1. Mode maintenance

```bash
php wp-cli.phar maintenance-mode activate
```
(ou une extension de maintenance si vous voulez une jolie page). À désactiver à
la toute fin : `php wp-cli.phar maintenance-mode deactivate`.

---

## 2. Installer le nouveau thème (sans encore désactiver Elementor)

```bash
php wp-cli.phar theme install generatepress
php wp-cli.phar plugin install generateblocks --activate
# thème enfant : déposer le dossier wp-content/themes/lacrochardiere/ (fourni
# dans ce dépôt), puis :
php wp-cli.phar theme activate lacrochardiere
```

> On garde **Elementor actif** pour l'instant : tant que les pages ne sont pas
> reconstruites, leur contenu vit dans `_elementor_data` et ne s'affiche qu'avec
> Elementor. On le retirera à l'étape 6.

Réglages globaux GeneratePress : **Apparence → Customizer** (couleurs,
typographie, largeur de contenu, logo, entête/pied). Le thème enfant fourni
centralise vos CSS custom dans `style.css`.

---

## 3. Inventaire du contenu à reconstruire

```bash
php wp-cli.phar post list --post_type=page --fields=ID,post_title,post_name,post_status
php wp-cli.phar menu list
```

Les 10 pages cibles :

| Page | Type de contenu | Points d'attention |
|------|-----------------|--------------------|
| Accueil | Hero + intro + liens vers hébergements | Définir comme page d'accueil (Réglages → Lecture) |
| La propriété | Texte + galerie | Bloc Galerie natif |
| Hébergement 1 | Texte + photos + (dispo Gîtes de France ?) | Voir §5 pour l'embed |
| Hébergement 2 | idem | |
| Hébergement 3 | idem | |
| Contenu touristique 1 | Article/contenu | |
| Contenu touristique 2 | idem | |
| Contenu touristique 3 | idem | |
| Contact | Formulaire | Voir §4 |
| Localiser | Carte | iframe OSM/Google Maps dans bloc HTML |

> **SEO — important** : conservez les **mêmes slugs** (`post_name`) pour chaque
> page afin de ne pas casser les URLs indexées. Si un slug doit changer, posez
> une redirection **301** (extension « Redirection », ou règle `.htaccess`).

---

## 4. Formulaire de contact

Trois options, par ordre de légèreté :
- **Fluent Forms** (recommandé, léger, moderne) — `wp plugin install fluentform --activate`
- **Contact Form 7** (minimal, éprouvé) — `wp plugin install contact-form-7 --activate`
- **Garder Formidable** (déjà en place ; plus lourd, mais zéro changement).

Reconstituez : nom, e-mail, message + protection anti-spam (Akismet déjà présent,
ou un simple honeypot). Vérifiez la **réception réelle** des e-mails (OVH +
adresse d'expédition valide du domaine, sinon utiliser SMTP).

---

## 5. Widget Gîtes de France (disponibilité / réservation)

C'est un **code d'intégration** (iframe ou script) fourni dans votre espace
propriétaire Gîtes de France. Insertion via un bloc **HTML personnalisé** sur la
page hébergement concernée :

```html
<!-- Coller ici le snippet officiel Gîtes de France (iframe/script) -->
<div class="lc-dispo">
  <!-- ex : <iframe src="https://widget.gites-de-france.com/..." ...></iframe> -->
</div>
```

> Si le widget est un `<script>` externe : vérifiez qu'il se charge en **HTTPS**
> (pas de contenu mixte) et, s'il est bloqué par une CSP, ajustez l'en-tête.

---

## 6. Bascule finale & retrait d'Elementor

Une fois les 10 pages reconstruites et validées :

```bash
# Vérifier qu'aucune page publiée ne dépend encore d'Elementor
php wp-cli.phar post list --post_type=page --meta_key=_elementor_edit_mode --fields=ID,post_title

# Retirer Elementor et toute la machinerie hôtelière devenue inutile
php wp-cli.phar plugin deactivate elementor elementor-pro motopress-hotel-booking \
  woocommerce th-widget-pack envato-market kirki 2>/dev/null
php wp-cli.phar plugin delete elementor elementor-pro motopress-hotel-booking \
  woocommerce th-widget-pack envato-market kirki 2>/dev/null

# Supprimer l'ancien thème Bellevue (parent + enfant)
php wp-cli.phar theme delete bellevuex bellevuex-child 2>/dev/null
```

> Adaptez la liste à ce que renvoie `wp plugin list --status=active`. Ne retirez
> Aloha PowerPack, MonsterInsights, WPCode que si vous ne vous en servez pas.
> **Gardez** GeneratePress, GenerateBlocks, votre plugin de formulaire, Akismet.

Nettoyer les tables devenues orphelines (optionnel, après avoir vérifié le site) :
la désinstallation propre des plugins via WP-CLI supprime généralement leurs
options ; un passage d'un plugin type « Advanced Database Cleaner » peut finir le
travail.

---

## 7. Durcissement & perf (rappel)

- `.htaccess` durci : voir `docs/htaccess-recommande.txt` (compression + cache).
- Bloc PHP interdit dans `wp-content/uploads/` (déjà posé pendant le nettoyage).
- `define('DISALLOW_FILE_EDIT', true);` dans `wp-config.php`.
- **Wordfence** installé + scan complet (confirmation propreté post-migration).
- Cache : GeneratePress est déjà rapide ; un cache page (ex. cache OVH, ou
  « WP Super Cache » léger) finit le travail.
- Mises à jour auto activées pour core + plugins.

---

## 8. Sortie de maintenance & vérifs finales

```bash
php wp-cli.phar maintenance-mode deactivate
```

Checklist de recette :
- [ ] Les 10 pages s'affichent, mêmes URLs qu'avant.
- [ ] Menu + page d'accueil OK.
- [ ] Formulaire de contact → e-mail bien reçu.
- [ ] Widget Gîtes de France fonctionnel (dispo/réservation).
- [ ] Carte de localisation OK.
- [ ] Responsive mobile.
- [ ] Page 404 correcte, pas de lien mort (crawl rapide).
- [ ] Lighthouse : perf + accessibilité.
- [ ] Wordfence : « no threats ».

---

## Résumé de l'état cible

**Avant** : Bellevue (premium, hôtelier) + Elementor + MPHB + WooCommerce + Aloha
+ Kirki + Envato Market + TH Widget Pack…
**Après** : GeneratePress + GenerateBlocks + thème enfant `lacrochardiere` +
1 plugin de formulaire + Akismet + Wordfence. Plus léger, plus rapide, bien
moins de surface d'attaque, et éditable en blocs natifs sans page builder.
