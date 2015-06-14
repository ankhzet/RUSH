## RUSH RPG
============

RUSH is opensorce RPG written in PHP and based on Laravel framework.

Installation
----------------

### Technical requirements

- Any webserver, that meets Laravel 5 requirements


### Installation Instructions

- Clone this repo
- Pull app dependencies via composer:
```bash
machine:rush user$ cd rush/
machine:rush user$ composer install
machine:rush user$ chmod -R a+rw storage
machine:rush user$ chmod -R a+rw bootstrap/cache
```
- Configure database (you can do that while dependencies are downloading):
```bash
machine:rush user$ sudo mysql
mysql> CREATE USER 'rush'@'localhost' IDENTIFIED BY 'rush';
mysql> SET PASSWORD FOR 'rush'@'localhost' = PASSWORD('secret')
mysql> CREATE DATABASE rush_rpg_db;
mysql> GRANT ALL PRIVILEGES ON rush_rpg_db . * TO 'rush'@'localhost';
mysql> FLUSH PRIVILEGES;
```
Note: If yours database/user preferences differs, dont forget to make changes in `.env` file.
- Create all required database tables and seed them with data with artisan:
```bash
machine:rush user$ php artisan migrate --seed
```
- Now you can open site in browser and login as `ankhzet@gmail.com`, password `password`
Note: You can change database/seeds/UsersTableSeeder.php to seed database with yours credentials.

### Implementation progress

- [x] Users/Roles (partial)
- [] Game core
	- [] Creatures
		- [] Creatures
		- [] Players
		- [] NPC's
	- [] Fractions
		- [] Fraction relations
		- [] Reputation systems
	- [] Items
		- [] Item effects
		- [] Usable Items (in conjunction with spellsystem)
	- [] Inventory
		- [] Bags
	- [] Equipment
		- [] Equipment summarized effects
	- [] Quests
		- [] Quest branches
		- [] Quest nodes
			- [] Quest node objectives
		- [] Quest rewards
			- [] Choosable quest rewards
	- [] Locations
		- [] Teleporting system
		- [] Homebind system
		- [] Cinematic locations
	- [] Spellsystem
		- [] Spell
		- [] Spellcast
		- [] Auras, DoT's, HoT's, chanelled spels
	- [] Battlesystem
		- [] Honor system
		- [] Hostile/friendly tests
		- [] Battle initiation/join/flee etc.
		- [] Attack queue/chain/loop
	- [] Loot tables
- [] Socials?
	- [] Party/Raid
		- [] Group interface
	- [] Chat
- [] Helpsystem
	- [] Tutorials system
	- [] Ticketing
- [] Game masters
	- [] Tickets
	- [] Bans/mutes
- [] Game designers
	- [] Editors interface
		- [] Creature editors
		- [] Location editors
		- [] NPC editors
		- [] Fraction editors
		- [] Item editors
			- [] Item effects editors
		- [] Spell editors
		- [] Quest editors
		- [] Loot table editors

More to come...

### Change notes

- Partial implementation on handmade framework
- Ported to Laravel 4.2
- Wiped, porting to Laravel 5
