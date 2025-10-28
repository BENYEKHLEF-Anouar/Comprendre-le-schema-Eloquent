# 2.1.4 — Requêtes CRUD avec Eloquent

---

## Glossaire minute

| Terme | Définition |
|-------|-----------|
| **CRUD** | Opérations de base sur une ressource : Create, Read, Update, Delete |
| **Eloquent** | ORM de Laravel permettant d’interagir avec la base via des objets PHP |
| **Tinker** | Console interactive pour exécuter des commandes Laravel |
| **Scope** | Filtre réutilisable sur un modèle |
| **Query chaining** | Enchaînement fluide de méthodes (ex. `where()->orderBy()->get()`) |

---

## Objectif pédagogique

Savoir manipuler la base de données du **Blog Laravel** via Tinker et Eloquent :

- Créer des données (`create`, `save`)  
- Lire des données (`all`, `find`, `where`, `with`)  
- Mettre à jour (`update`, `save`)  
- Supprimer (`delete`)  
- Gérer les relations et les requêtes chaînées

---

## Définition théorique

Eloquent agit comme une **couche intermédiaire** entre PHP et MySQL :

- Chaque **modèle** représente une table
- Chaque instance correspond à une ligne dans cette table

| Action | SQL classique | Équivalent Eloquent |
|--------|---------------|-------------------|
| Lire | `SELECT * FROM articles;` | `Article::all();` |
| Filtrer | `WHERE user_id = 1;` | `Article::where('user_id', 1)->get();` |
| Insérer | `INSERT INTO articles ...` | `Article::create([...]);` |
| Modifier | `UPDATE articles ...` | `$article->update([...]);` |
| Supprimer | `DELETE FROM articles ...` | `$article->delete();` |

---

## Tutoriel pratique

### Étape 1 — Lancer Tinker

```bash
php artisan tinker
```

---

### Étape 2 — Créer des données (Create)

#### Méthode 1 — Avec `create()`

```php
>>> use App\Models\Article;
>>> $article = Article::create([
... 'user_id' => 1,
... 'title' => 'Premier article manuel',
... 'slug' => 'premier-article',
... 'excerpt' => 'Introduction à Eloquent CRUD',
... 'content' => 'Ceci est un test d’ajout via Tinker.'
... ]);
```

💡 Les champs doivent être déclarés dans `$fillable` du modèle.

#### Méthode 2 — Avec `new` et `save()`

```php
>>> $a = new Article;
>>> $a->user_id = 1;
>>> $a->title = 'Deuxième article';
>>> $a->slug = 'deuxieme-article';
>>> $a->save();
```

✅ Vérification :

```php
>>> Article::count();
```

---

### Étape 3 — Lire des données (Read)

#### Lister tous les articles

```php
>>> Article::all();
```

#### Trouver un article précis

```php
>>> Article::find(1);
```

#### Filtrer par mot-clé

```php
>>> Article::where('title','like','%article%')->get();
```

#### Lire avec les relations (user, tags)

```php
>>> Article::with(['user','tags'])->first();
```

#### Compter les articles par utilisateur

```php
>>> \App\Models\User::withCount('articles')->get();
```

---

### Étape 4 — Modifier des données (Update)

```php
>>> $article = Article::find(1);
>>> $article->update(['title' => 'Titre modifié']);

>>> $article->title = 'Nouveau titre modifié';
>>> $article->save();
```

✅ Vérification :

```php
>>> Article::find(1)->title;
```

---

### Étape 5 — Supprimer des données (Delete)

```php
>>> $article = Article::find(1);
>>> $article->delete();
```

💡 Vérification :

```php
>>> Article::find(1);
null
```

---

### Étape 6 — Manipuler les relations

#### Ajouter un tag à un article

```php
>>> $a = Article::first();
>>> $a->tags()->attach(1);
```

#### Retirer un tag

```php
>>> $a->tags()->detach(1);
```

#### Remplacer tous les tags

```php
>>> $a->tags()->sync([2,3,4]);
```

---

### Étape 7 — Filtrer et trier (Query Builder)

#### Trier par date

```php
>>> Article::orderBy('created_at','desc')->take(5)->get();
```

#### Sélectionner certains champs

```php
>>> Article::select('id','title','slug')->get();
```

#### Combiner plusieurs conditions

```php
>>> Article::where('user_id',1)->orderBy('title')->limit(3)->get();
```

---

### Étape 8 — Créer un scope personnalisé (optionnel)

Dans `app/Models/Article.php` :

```php
public function scopeRecent($query)
{
    return $query->orderBy('created_at', 'desc')->take(5);
}
```

Dans Tinker :

```php
>>> Article::recent()->get();
```

---

### Bonus — Requêtes avancées

```php
>>> Article::where('title','like','%laravel%')
... ->selectRaw('user_id, count(*) as total')
... ->groupBy('user_id')
... ->get();
```

---

## Résumé et points-clés

| Action | Méthode Eloquent | Exemple |
|--------|-----------------|---------|
| Créer | `create()` / `save()` | `Article::create([...])` |
| Lire | `all()`, `find()`, `where()` | `Article::where('user_id',1)->get()` |
| Mettre à jour | `update()` | `$article->update([...])` |
| Supprimer | `delete()` | `$article->delete()` |
| Relations | `with()`, `attach()`, `sync()` | `$a->tags()->sync([1,2])` |
| Scopes | `scopeNom()` | `Article::recent()->get()` |

---

**En résumé :**
> Eloquent simplifie les opérations CRUD et la gestion des relations,  
> avec la possibilité de chaîner les requêtes, créer des scopes et manipuler les données directement depuis **Tinker**.
