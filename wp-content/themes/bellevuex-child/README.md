# Thème enfant Bellevue (`bellevuex-child`)

Ce thème enfant est le **seul endroit** où vous devez faire vos personnalisations.

## Pourquoi ?
Le thème parent `bellevuex` est un thème premium tiers (Themovation). Chaque
mise à jour du thème **écrase tous ses fichiers**. Si vous modifiez le parent
directement, vous perdez vos changements à la prochaine mise à jour — et vous
êtes tenté·e de ne plus mettre à jour, ce qui ouvre des failles de sécurité
(ce thème a déjà eu une vulnérabilité « Broken Access Control » ≤ 4.2.2).

Le thème enfant règle ce problème : le parent reste vierge et à jour, vos
changements vivent ici.

## Comment l'activer
1. Copier le dossier `bellevuex-child/` dans `wp-content/themes/` sur le serveur OVH.
2. Dans l'admin WordPress : **Apparence → Thèmes**, activer **« Bellevue Child »**.
3. Vérifier que le site s'affiche normalement (l'apparence ne doit pas changer :
   le style du parent est hérité automatiquement).

> ⚠️ Après activation, refaire les réglages du *Customizer* seulement si nécessaire :
> les `theme_mods` sont propres à chaque thème. Testez d'abord sur un site de
> préproduction si possible.

## Comment personnaliser
- **CSS** → dans `style.css` (chargé après le parent, vos règles gagnent).
- **PHP / hooks / filtres** → dans `functions.php`.
- **Surcharger un template** → copier le fichier depuis `bellevuex/` vers
  `bellevuex-child/` en gardant le même chemin relatif, puis l'éditer.
  Exemple : pour changer le pied de page, copier `bellevuex/footer.php`
  vers `bellevuex-child/footer.php`.

## Règle d'or
On ne touche **jamais** à `wp-content/themes/bellevuex/`. Tout se passe ici.
