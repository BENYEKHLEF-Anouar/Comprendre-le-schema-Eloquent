````markdown
# 2.1.3 — Seeders & Factories Eloquent

> **Prérequis :**
> - Les modèles et relations `User`, `Article` et `Tag` doivent déjà être fonctionnels  
>   (voir les tutoriels **2.1.1 — Migrations & Modèles Eloquent** et **2.1.2 — Relations Eloquent**).

---

## Glossaire minute

| Terme | Définition |
|--------|-------------|
| **Factory** | Modèle décrivant la forme des données fictives à générer |
| **Seeder** | Script d’insertion automatique dans la base de données |
| **Faker** | Générateur intégré de textes, emails, dates et contenus aléatoires |
| **firstOrCreate()** | Crée une donnée uniquement si elle n’existe pas encore (idempotent) |
| **sync()** | Associe plusieurs enregistrements dans une relation **n–n** (`Article ↔ Tag`) |

---

## Objectif pédagogique

Apprendre à automatiser la création de données réalistes et cohérentes pour le projet **Blog Laravel**, en respectant les relations entre modèles :

- Générer des utilisateurs (`UserFactory`)
- Générer des tags (`TagFactory`)
- Générer des articles liés à un utilisateur (`ArticleFactory`)
- Associer automatiquement les articles ↔ tags via `sync()`

---

## Définition théorique

Laravel propose un système combiné **Factory + Seeder** pour faciliter le remplissage des bases de données pendant le développement :

| Élément | Rôle |
|----------|------|
| **Factory** | Définit la structure type des données à générer |
| **Seeder** | Ordonne la création et l’insertion des données |
| **DatabaseSeeder** | Coordonne l’exécution de tous les seeders |
| **Faker** | Produit des valeurs aléatoires réalistes (titres, contenus, emails...) |

**Commande clé** pour réinitialiser et recharger la base :
```bash
php artisan migrate:fresh --seed
```

---

## Tutoriel pratique

### Étape 1 — Créer les **Factories**

Les factories décrivent la **forme des données générées automatiquement** pour chaque modèle.

```bash
php artisan make:factory UserFactory --model=User
php artisan make:factory TagFactory --model=Tag
php artisan make:factory ArticleFactory --model=Article
```

---

#### 📄 `database/factories/UserFactory.php`

Génère de faux utilisateurs avec des emails uniques.

```php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
        ];
    }
}
```

---

#### 📄 `database/factories/TagFactory.php`

Crée des **tags uniques** avec nom et slug.

```php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TagFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word();
        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
        ];
    }
}
```

---

#### 📄 `database/factories/ArticleFactory.php`

Crée des **articles liés à un utilisateur existant**.

```php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;

class ArticleFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);
        return [
            'user_id' => User::inRandomOrder()->value('id') ?? 1,
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->sentence(12),
            'content' => fake()->paragraphs(3, true),
        ];
    }
}
```

---

### Étape 2 — Créer les **Seeders**

Les seeders orchestrent la **création et insertion des données** dans le bon ordre.

```bash
php artisan make:seeder UserSeeder
php artisan make:seeder TagSeeder
php artisan make:seeder ArticleSeeder
php artisan make:seeder PivotArticleTagSeeder
```

---

#### 📄 `database/seeders/UserSeeder.php`

```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->count(5)->create();
    }
}
```

---

#### 📄 `database/seeders/TagSeeder.php`

```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        Tag::factory()->count(10)->create();
    }
}
```

---

#### 📄 `database/seeders/ArticleSeeder.php`

```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        Article::factory()->count(20)->create();
    }
}
```

---

#### 📄 `database/seeders/PivotArticleTagSeeder.php`

Associe **1 à 4 tags aléatoires** à chaque article.

```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\Tag;

class PivotArticleTagSeeder extends Seeder
{
    public function run(): void
    {
        $tagIds = Tag::pluck('id');

        Article::all()->each(function ($article) use ($tagIds) {
            $article->tags()->sync($tagIds->random(rand(1, 4))->all());
        });
    }
}
```

---

### Étape 3 — Orchestration avec **DatabaseSeeder**

```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            TagSeeder::class,
            ArticleSeeder::class,
            PivotArticleTagSeeder::class,
        ]);
    }
}
```

---

### Étape 4 — Exécution des seeders

Réinitialiser et remplir la base automatiquement :

```bash
php artisan migrate:fresh --seed
```

Si tout fonctionne, la console affichera :

```
Seeding: UserSeeder
Seeding: TagSeeder
Seeding: ArticleSeeder
Seeding: PivotArticleTagSeeder
```

---

### Étape 5 — Vérification dans **Tinker**

```bash
php artisan tinker
```

Commandes utiles :

```php
>>> App\Models\User::count();        // 5 utilisateurs
>>> App\Models\Article::count();     // 20 articles
>>> App\Models\Tag::count();         // 10 tags
>>> App\Models\Article::first()->tags->pluck('name'); // tags associés
>>> App\Models\User::first()->articles->count();      // articles d’un user
```

Si toutes ces commandes renvoient des données, les seeders fonctionnent.

---

## Résumé et points-clés

| Élément | Rôle | Exemple |
|----------|------|----------|
| **Factory** | Définit la structure des données fictives | `TagFactory` |
| **Seeder** | Exécute la génération de données dans le bon ordre | `TagSeeder` |
| **DatabaseSeeder** | Coordonne tous les seeders | `$this->call([...])` |
| **Faker** | Génère des données réalistes | `fake()->sentence()` |
| **sync()** | Lie plusieurs entités dans une table pivot | `$article->tags()->sync([...])` |

---

## Bonus — Commandes pratiques

| Action | Commande |
|--------|-----------|
| (Re)créer la base + données | `php artisan migrate:fresh --seed` |
| Lancer un seul seeder | `php artisan db:seed --class=UserSeeder` |
| Créer une factory liée à un modèle | `php artisan make:factory ArticleFactory --model=Article` |
| Créer un seeder | `php artisan make:seeder ArticleSeeder` |

---

**En résumé :**
> Les **Factories** définissent la *forme* des données,  
> Les **Seeders** les *insèrent*,  
> Et **DatabaseSeeder** orchestre l’ensemble pour peupler la base automatiquement.

---

**Migration** : Une classe PHP qui définit les modifications du schéma de base de données (par exemple, la création de tables, l'ajout de colonnes). Elle fonctionne comme un script SQL avec contrôle de version.
**Model** : Une classe Eloquent (par exemple, User.php) qui représente une table de base de données et fournit une interface ORM (Object-Relational Mapping) pour les opérations CRUD.
**Factory** : Une classe qui génère de fausses instances d'un modèle (par exemple, UserFactory.php) à des fins de test ou d'amorçage.
**Faker** : Une bibliothèque PHP (intégrée via fakerphp/faker) qui génère des données factices réalistes (par exemple, des noms, des adresses e-mail) utilisées dans les usines.
**Seeder** : Une classe qui exécute des usines (ou insère directement des données) pour remplir la base de données avec des exemples d'enregistrements pendant le développement ou les tests.

````
---
