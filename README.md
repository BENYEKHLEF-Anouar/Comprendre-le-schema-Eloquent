````markdown
# 2.1.2 — Déclaration des relations Eloquent

> **Prérequis :**
> - Les migrations et modèles `User`, `Article` et `Tag` doivent être créés (voir le tutoriel **2.1.1 — Migrations & Modèles Eloquent**).
> - Base MySQL fonctionnelle et synchronisée.

---

## Glossaire minute

| Terme | Définition |
|--------|-------------|
| **Relation 1-n** (`hasMany` / `belongsTo`) | Un parent possède plusieurs enfants (ex. `User → Articles`) |
| **Relation n-n** (`belongsToMany`) | Deux entités reliées via une table pivot (ex. `Article ↔ Tag`) |
| **Pivot** | Table intermédiaire contenant les identifiants des deux entités (`article_tag`) |
| **Eager loading** | Chargement anticipé des relations pour éviter le problème N+1 |

---

## Objectif pédagogique

Déclarer et tester les relations entre modèles Eloquent du projet **Blog** :

- `User → hasMany(Article)`
- `Article → belongsTo(User)`
- `Article ↔ Tag` via `belongsToMany`
- Utiliser `with()` et `withCount()` pour interroger efficacement les relations.

---

## Définition théorique

| Relation | Exemple | Description |
|-----------|----------|-------------|
| **1 → n** | `User → Article` | Un utilisateur possède plusieurs articles |
| **n → 1** | `Article → User` | Un article appartient à un utilisateur |
| **n ↔ n** | `Article ↔ Tag` | Plusieurs articles peuvent avoir plusieurs tags |

💡 Ces relations permettent une navigation fluide entre les entités :

```php
$user->articles;   // articles de l’utilisateur
$article->user;    // auteur de l’article
$article->tags;    // tags associés à l’article
$tag->articles;    // articles associés à un tag
````

---

## Tutoriel pratique

### Étape 1 — Déclarer la relation `User → Article`

Un utilisateur peut écrire plusieurs articles : **hasMany()**

📄 `app/Models/User.php`

```php
public function articles()
{
    return $this->hasMany(Article::class);
}
```

---

### Étape 2 — Déclarer la relation `Article → User`

Un article appartient à un seul utilisateur : **belongsTo()**

📄 `app/Models/Article.php`

```php
public function user()
{
    return $this->belongsTo(User::class);
}
```

---

### Étape 3 — Déclarer la relation `Article ↔ Tag` (n-n)

Un article peut avoir plusieurs tags, et un tag peut être associé à plusieurs articles.
Cette relation passe par la table pivot `article_tag`.

📄 `app/Models/Article.php`

```php
public function tags()
{
    return $this->belongsToMany(Tag::class);
}
```

📄 `app/Models/Tag.php`

```php
public function articles()
{
    return $this->belongsToMany(Article::class);
}
```

---

### Étape 4 — Charger les relations (**Eager loading**)

Évite le problème N+1 en chargeant les relations à l’avance.

```php
// Sans eager loading (risque N+1)
$articles = App\Models\Article::all();

// Avec eager loading
$articles = App\Models\Article::with(['user', 'tags'])->get();
```

Vérifie le nombre de requêtes dans :

* `storage/logs/laravel.log`
* ou via **Laravel Debugbar**

---

### Étape 5 — Ajouter un compteur de relation (**withCount**)

`withCount()` ajoute une colonne virtuelle `*_count` sur le modèle.

```php
$articles = App\Models\Article::withCount('tags')->get();

foreach ($articles as $a) {
    echo $a->title.' ('.$a->tags_count.' tags)';
}
```

---

### Étape 6 — Vérification dans **Tinker**

```bash
php artisan tinker
```

Commandes à tester :

```php
>>> $u = App\Models\User::first();
>>> $u->articles; // liste des articles du user

>>> $a = App\Models\Article::first();
>>> $a->user; // auteur de l’article
>>> $a->tags; // liste des tags liés

>>> $t = App\Models\Tag::first();
>>> $t->articles; // articles associés à ce tag

>>> App\Models\Article::with(['user','tags'])->withCount('tags')->first();

>>> App\Models\User::count();
>>> App\Models\Article::count();
>>> App\Models\Tag::count();
```

Si ces commandes renvoient des objets Eloquent, les relations fonctionnent.

---

## Bonus — Navigation entre relations

Afficher les tags du premier article d’un utilisateur :

```php
$user = App\Models\User::with('articles.tags')->first();

foreach ($user->articles as $article) {
    echo "Article : {$article->title}\n";
    echo "Tags : ". $article->tags->pluck('name')->join(', ') ."\n\n";
}
```

---

## Résumé et points-clés

| Relation          | Méthode                 | Exemple pratique                        |
| ----------------- | ----------------------- | --------------------------------------- |
| **1-n**           | `hasMany` / `belongsTo` | `$user->articles`, `$article->user`     |
| **n-n**           | `belongsToMany`         | `$article->tags`, `$tag->articles`      |
| **Eager loading** | `with()`                | `Article::with(['user','tags'])->get()` |
| **Compteur**      | `withCount()`           | `Article::withCount('tags')->first()`   |


| Relation      | Method in Model   | Code Example                            | Direction          |
| ------------- | ----------------- | --------------------------------------- | ------------------ |
| 1-n           | `hasMany()`       | `$user->articles`                       | User → Articles    |
| n-1           | `belongsTo()`     | `$article->user`                        | Article → User     |
| n-n           | `belongsToMany()` | `$article->tags`, `$tag->articles`      | Article ↔ Tag      |
| Eager Loading | `with()`          | `Article::with(['user','tags'])->get()` | Query Optimization |
| Counting      | `withCount()`     | `Article::withCount('tags')->get()`     | Relation Count     |


---



