````markdown
# 2.1.1 — Migrations & Modèles Eloquent

> **Prérequis :**
> - Projet Laravel 11 fonctionnel (`blog-laravel`)
> - Base de données MySQL configurée dans `.env`

---

## Objectif pédagogique

Créer un schéma relationnel complet pour un blog Laravel :

- Définir les tables et leurs relations physiques (**PK / FK**)
- Appliquer les contraintes d’intégrité (`unique`, `cascadeOnDelete`)
- Générer les modèles Eloquent correspondants (`Article`, `Tag`)
- Vérifier la cohérence dans **Tinker**

---

## Commandes principales

### 1. Créer les migrations

```bash
php artisan make:migration create_articles_table
php artisan make:migration create_tags_table
php artisan make:migration create_article_tag_table
````

---

### 2. Exécuter les migrations

```bash
php artisan migrate
```

Vérification MySQL :

```sql
SHOW TABLES;
SHOW CREATE TABLE articles;
```

---

### 3. Créer les modèles Eloquent

```bash
php artisan make:model Article
php artisan make:model Tag
```

Le modèle `User` est déjà présent par défaut dans `app/Models/User.php`.

---

### 4. Tester les modèles avec Tinker

```bash
php artisan tinker
```

Exemples de commandes à exécuter dans Tinker :

```php
>>> App\Models\Article::count();
>>> App\Models\Tag::create(['name' => 'Laravel', 'slug' => 'laravel']);
>>> App\Models\Tag::all();
```

Si la création et la récupération fonctionnent, le schéma et les modèles sont synchronisés.

---

## Structure du projet

```
database/
└── migrations/
    ├── 2014_10_12_000000_create_users_table.php
    ├── 2025_10_24_000001_create_articles_table.php
    ├── 2025_10_24_000002_create_tags_table.php
    └── 2025_10_24_000003_create_article_tag_table.php

app/
└── Models/
    ├── User.php
    ├── Article.php
    └── Tag.php
```

---

## Rappels — Schéma des tables

### `users`

```php
$table->id();
$table->string('name');
$table->string('email')->unique();
$table->string('password');
$table->timestamps();
```

### `articles`

```php
$table->id();
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
$table->string('title', 180);
$table->string('slug', 200)->unique();
$table->text('excerpt')->nullable();
$table->longText('content')->nullable();
$table->timestamps();
```

### `tags`

```php
$table->id();
$table->string('name')->unique();
$table->string('slug')->unique();
$table->timestamps();
```

### `article_tag` (table pivot)

```php
$table->foreignId('article_id')->constrained()->cascadeOnDelete();
$table->foreignId('tag_id')->constrained()->cascadeOnDelete();
$table->primary(['article_id', 'tag_id']);
```

---

## Types de colonnes utiles

| Type                     | Exemple                                          | Description                |
| ------------------------ | ------------------------------------------------ | -------------------------- |
| `string`                 | `$table->string('title', 255);`                  | Texte court (VARCHAR)      |
| `text`                   | `$table->text('content');`                       | Texte long                 |
| `integer` / `bigInteger` | `$table->integer('age');`                        | Nombre entier              |
| `boolean`                | `$table->boolean('is_active');`                  | Booléen                    |
| `decimal` / `float`      | `$table->decimal('price', 8, 2);`                | Nombre avec décimales      |
| `date` / `timestamp`     | `$table->timestamp('published_at');`             | Date et heure              |
| `enum`                   | `$table->enum('status', ['draft','published']);` | Liste de valeurs possibles |
| `json`                   | `$table->json('meta');`                          | Données JSON               |
| `foreignId`              | `$table->foreignId('user_id')->constrained();`   | Clé étrangère              |
| `softDeletes`            | `$table->softDeletes();`                         | Suppression logique        |

---

## Résumé

| Concept             | Description                       | Exemple                               |
| ------------------- | --------------------------------- | ------------------------------------- |
| **Migration**       | Structure versionnée d’une table  | `create_articles_table`               |
| **PK / FK**         | Clés d’intégrité référentielle    | `foreignId('user_id')->constrained()` |
| **Modèle Eloquent** | Classe PHP représentant une table | `Article`, `Tag`                      |
| **$fillable**       | Champs modifiables en masse       | `['title', 'slug']`                   |
| **Tinker**          | Console interactive Eloquent      | `php artisan tinker`                  |

---

```
