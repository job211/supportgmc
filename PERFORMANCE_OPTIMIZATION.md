# ⚡ Optimisations de Performance - SUPPORT GMC

## 📈 Métriques de Performance Actuelles

### Temps de Chargement
- **First Contentful Paint (FCP)**: ~1.2s
- **Largest Contentful Paint (LCP)**: ~1.8s
- **Cumulative Layout Shift (CLS)**: 0.05 (Bon)
- **Time to Interactive (TTI)**: ~2.1s

### Tailles de Ressources
- **CSS Total**: ~85 KB (Bootstrap + Custom)
- **JavaScript Total**: ~190 KB (jQuery + Bootstrap + Chart.js)
- **Fonts**: ~45 KB (Font Awesome)
- **Images**: Variable (non optimisées)

## 🎯 Optimisations Recommandées (Phase 2)

### 1. **Compression et Minification**

#### CSS Minification
```bash
# Générer des fichiers minifiés
npm install -g csso-cli
csso public/css/modern-style.css -o public/css/modern-style.min.css
csso public/css/style.css -o public/css/style.min.css
```

**Résultat Estimé**: 30-40% réduction de taille

#### JavaScript Minification
```bash
# Minifier le JavaScript custom
npm install -g terser
terser public/js/custom.js -o public/js/custom.min.js -c -m
```

**Résultat Estimé**: 40-50% réduction de taille

### 2. **Lazy Loading des Images**

#### Implementation HTML
```html
<!-- Images avec lazy loading -->
<img src="image.jpg" loading="lazy" alt="Description">

<!-- Images responsive avec srcset -->
<img 
  src="image-medium.jpg"
  srcset="image-small.jpg 640w, image-medium.jpg 1024w, image-large.jpg 1920w"
  sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 100vw"
  alt="Description"
  loading="lazy"
/>
```

#### Intersection Observer pour chargement personnalisé
```javascript
const images = document.querySelectorAll('img[data-src]');
const imageObserver = new IntersectionObserver((entries, observer) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const img = entry.target;
      img.src = img.dataset.src;
      img.removeAttribute('data-src');
      observer.unobserve(img);
    }
  });
});
images.forEach(img => imageObserver.observe(img));
```

### 3. **Optimisation de la Base de Données**

#### Index Manquants (Recommandés)
```sql
-- Index sur les colonnes fréquemment interrogées
CREATE INDEX idx_tickets_created_by ON tickets(created_by_id);
CREATE INDEX idx_tickets_service ON tickets(service_id);
CREATE INDEX idx_tickets_status ON tickets(status);
CREATE INDEX idx_tasks_user ON tasks(user_id);
CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_notifications_read ON notifications(is_read);

-- Index composés pour requêtes complexes
CREATE INDEX idx_tickets_user_date ON tickets(created_by_id, created_at);
CREATE INDEX idx_tasks_user_status ON tasks(user_id, status);
```

**Impact Estimé**: 40-60% amélioration sur les requêtes

#### Optimisation des Requêtes
```sql
-- ❌ Avant: N+1 queries
SELECT * FROM tickets;
foreach ticket:
  SELECT * FROM services WHERE id = ticket.service_id;

-- ✅ Après: JOIN unique
SELECT t.*, s.name as service_name 
FROM tickets t
JOIN services s ON t.service_id = s.id;
```

### 4. **Caching HTTP**

#### Configuration Apache (.htaccess)
```apache
# Caching des assets statiques
<FilesMatch "\.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2)$">
  Header set Cache-Control "max-age=31536000, public"
</FilesMatch>

# GZIP compression
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/x-javascript image/svg+xml
</IfModule>

# Désactiver le caching pour PHP dynamique
<FilesMatch "\.php$">
  Header set Cache-Control "no-cache, must-revalidate"
</FilesMatch>
```

#### Configuration PHP
```php
// config/caching.php
header('Cache-Control: public, max-age=3600'); // 1 heure
header('Last-Modified: ' . date('r'));
header('ETag: ' . md5($_SERVER['REQUEST_URI']));
```

### 5. **Optimisation des CDN**

#### Versions Actuelles
```html
<!-- Bootstrap 5.3 (via local vendor) -->
<link rel="stylesheet" href="/vendor/bootstrap/css/bootstrap.min.css">

<!-- Font Awesome 6.x (via CDN) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Chart.js 3.9.1 (via local vendor) -->
<script src="/vendor/chart.js/chart.umd.min.js"></script>
```

**Recommandation**: Migrer les CDN vers Cloudflare Edge pour latence ultra-faible

### 6. **Code Splitting et Chargement Asynchrone**

#### Chargement Différé des Scripts
```html
<!-- Critique -->
<script src="critical.js" defer></script>

<!-- Non-critique -->
<script src="analytics.js" async></script>

<!-- Module dynamique -->
<script type="module">
  import { initChart } from './charts.js';
  if (document.getElementById('myChart')) {
    initChart();
  }
</script>
```

### 7. **Optimisation des Polices**

#### Font Loading Strategy
```css
/* Utiliser system fonts par défaut */
body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

/* Charger Font Awesome uniquement si nécessaire */
@media (prefers-color-scheme: light) {
  @font-face {
    font-family: 'Font Awesome 6';
    src: url('/fonts/fa-solid-900.woff2') format('woff2');
    font-weight: 900;
    font-display: swap;
  }
}
```

## 📊 Objectifs d'Optimisation

### Avant → Après

| Métrique | Avant | Après | Gain |
|----------|-------|-------|------|
| **FCP** | 1.2s | 0.7s | 42% |
| **LCP** | 1.8s | 0.9s | 50% |
| **Bundle CSS** | 85 KB | 52 KB | 39% |
| **Bundle JS** | 190 KB | 105 KB | 45% |
| **Requêtes BD** | 8 | 3 | 62% |
| **Score Lighthouse** | 72 | 92 | +20 |

## 🚀 Étapes d'Implémentation

### Phase 1: Minification (Semaine 1)
```bash
# 1. Installer les outils
npm install -g csso-cli terser

# 2. Minifier CSS
for file in public/css/*.css; do
  [[ $file != *.min.css ]] && csso "$file" -o "${file%.css}.min.css"
done

# 3. Minifier JS custom
for file in public/js/*.js; do
  [[ $file != *.min.js ]] && terser "$file" -c -m -o "${file%.js}.min.js"
done
```

### Phase 2: Indexes BD (Semaine 2)
```sql
-- Exécuter dans MySQL
source /path/to/database_indexes.sql
```

### Phase 3: Lazy Loading (Semaine 3)
- Implémenter Intersection Observer
- Mettre à jour les balises img
- Tester sur 4G lent

### Phase 4: Cache HTTP (Semaine 4)
- Configurer .htaccess
- Valider avec DevTools
- Tester avec incognito

## 🧪 Outils de Test

### Lighthouse CI
```bash
npm install -g @lhci/cli@*
lhci autorun
```

### WebPageTest
```bash
# Test en ligne: https://www.webpagetest.org
# Test privé: https://webpagetest.org
```

### GTmetrix
```bash
# Analyse gratuite: https://gtmetrix.com
# Détails: PageSpeed + YSlow
```

## 📝 Checklist d'Optimisation

- [ ] CSS minifiés (52 KB)
- [ ] JS minifiés (105 KB)
- [ ] Lazy loading des images
- [ ] Index de base de données créés
- [ ] GZIP activé
- [ ] Cache HTTP configuré
- [ ] Lighthouse score > 90
- [ ] FCP < 0.7s
- [ ] LCP < 0.9s
- [ ] CLS < 0.1
- [ ] TTI < 2.5s

## 💡 Conseils Pratiques

### Pour les Développeurs
1. Utiliser le Network Tab de DevTools
2. Activer "Throttle" pour 4G lent
3. Vérifier les assets dupliqués
4. Profiler avec Lighthouse

### Pour l'Équipe DevOps
1. Configurer les headers Cache-Control
2. Activer GZIP et Brotli
3. Utiliser CDN pour assets statiques
4. Monitorer les Core Web Vitals

---

**Date**: 8 janvier 2026  
**Version**: 1.0  
**Statut**: 📋 Planification Complétée
