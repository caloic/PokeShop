-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : ven. 21 fév. 2025 à 09:52
-- Version du serveur : 8.0.40
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `php_exam_cano`
--

-- --------------------------------------------------------

--
-- Structure de la table `articles`
--

CREATE TABLE `articles` (
  `id` int NOT NULL,
  `nom` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `image_url` varchar(255) DEFAULT NULL,
  `date_publication` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modification` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `prix` decimal(10,2) NOT NULL,
  `user_id` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `articles`
--

INSERT INTO `articles` (`id`, `nom`, `slug`, `description`, `image_url`, `date_publication`, `date_modification`, `prix`, `user_id`, `deleted_at`, `is_deleted`) VALUES
(12, 'Bulbizarre', 'bulbizarre', 'Bulbizarre est un petit quadrupède vert avec une tête large. Il porte un bulbe sur son dos. Ce dernier lui sert également d\'organe de stockage, puisqu\'on rapporte notamment qu\'en période de sécheresse, il peut survivre plusieurs jours sans manger grâce à l\'énergie qui y est accumulée. Il a des taches foncées sur le corps faisant penser à un batracien. Son bulbe grandit en permanence en absorbant les rayons du soleil, et lorsque le poids du bulbe sera trop grand et empêchera Bulbizarre de se dresser sur ses deux pattes arrière, cela signifiera que son évolution en Herbizarre est proche.\r\n\r\nBulbizarre utilise couramment la capacité Vampigraine qui est l\'expulsion d\'une graine parasitant l\'ennemi par l\'orifice de son bulbe ; et le Fouet Lianes qui est l\'utilisation de tiges comme membres articulés pour frapper l\'adversaire. Ces tiges sont d\'ailleurs fréquemment utilisées pour manipuler des objets ou se porter lui-même en hauteur. Leur force est incroyable, il peut soulever des masses équivalentes à la sienne, voire plus grosses.', 'https://www.pokepedia.fr/images/thumb/e/ef/Bulbizarre-RFVF.png/500px-Bulbizarre-RFVF.png', '2025-02-21 07:59:54', '2025-02-21 07:59:54', 5.00, 3, NULL, 0),
(13, 'Herbizarre', 'herbizarre', 'Herbizarre est un Pokémon quadrupède, semblable à un dinosaure. Il a un corps bleu vert avec des taches plus foncées. Deux canines supérieures dépassent de sa bouche quand elle est fermée, et par rapport à sa pré-évolution, ses yeux sont plus petits et plus foncés. Il a sur le haut de sa tête deux oreilles pointues remplies de noir. Il a un petit museau rond et une large bouche. Chacun de ses pieds possède trois griffes pointues et blanches. Le bulbe sur son dos a fleuri et est devenu un gros bourgeon rose dont le poids est tel qu\'il empêche Herbizarre de se tenir sur ses pattes postérieures trop longtemps. Une petite tige marron, entourée par quatre larges feuilles, soutient le bourgeon.\r\n\r\nQuand le bourgeon est sur le point d\'éclore, il dégage une délicate odeur fleurie et commence à se gonfler. Herbizarre commence donc à passer plus de temps au soleil pour préparer son évolution proche. L\'exposition au soleil donne plus de force à la plante et à Herbizarre. Les Herbizarre vivent naturellement dans les plaines. Cependant, à présent, beaucoup vivent en captivité.', 'https://www.pokepedia.fr/images/thumb/4/44/Herbizarre-RFVF.png/500px-Herbizarre-RFVF.png', '2025-02-21 08:00:28', '2025-02-21 08:00:28', 10.00, 3, NULL, 0),
(14, 'Florizarre', 'florizarre', 'Florizarre est un imposant quadrupède à la peau verte ayant l\'aspect d\'un batracien, il est beaucoup plus grand et lourd que Bulbizarre et Herbizarre. Il porte une grande fleur rose tachetée sur son dos, entourée de quatre grandes feuilles. L\'intérieur de ses oreilles s\'est coloré de rouge et sa peau au niveau de ses pattes est désormais recouverte d\'excroissances faisant penser aux verrues d\'un crapaud. Sa bouche est ornée de six petites canines. Chacun de ses pas provoque un tremblement du sol. Sa fleur émet un parfum qui calme les esprits et appâte les Pokémon. Ce parfum est plus entêtant après une journée de pluie. La fleur permet aussi à Florizarre d\'absorber les rayons du soleil pour se soigner ou être plus efficace en combat.\r\n\r\nFlorizarre possède plus de lianes que ses pré-évolutions, qui étaient limitées à deux. Avec elles, il peut soulever des adversaires très lourds. Lors d\'une attaque Lance-Soleil, sa fleur sur son dos emmagasine l\'énergie des rayons du soleil, mais il semblerait qu\'il puisse relâcher sa puissance soit par l\'orifice de sa plante, soit par sa bouche, selon la position angulaire de son adversaire.', 'https://www.pokepedia.fr/images/thumb/4/42/Florizarre-RFVF.png/500px-Florizarre-RFVF.png', '2025-02-21 08:00:56', '2025-02-21 08:00:56', 15.00, 3, NULL, 0),
(15, 'Salamèche', 'salamèche', 'Salamèche est un Pokémon bipède et reptilien avec un corps principalement orange, à l\'exception de son ventre et de ses plantes de pieds qui sont beiges. Ses bras et ses jambes sont courts, avec respectivement quatre doigts et trois griffes chacune. Une flamme brûle au bout de la svelte queue de Salamèche, et elle flamboie depuis sa naissance. La flamme peut servir d\'indication quant à la santé et à l\'humeur de Salamèche : elle brûle fièrement quand le Pokémon est en pleine forme, doucement si le Pokémon est faible ou triste, ondoie quand il est heureux et flamboie quand il est en colère. Il est dit qu\'un Salamèche meurt si sa flamme s\'éteint.\r\n\r\nSalamèche peut être trouvé dans les chaudes aires montagneuses. Cependant, il est trouvé encore plus fréquemment sous la propriété d\'un Dresseur. Comme montré dans Pokémon Snap, Salamèche a un comportement de groupe, appelant les autres membres de sa bande quand il trouve à manger.', 'https://www.pokepedia.fr/images/thumb/8/89/Salam%C3%A8che-RFVF.png/500px-Salam%C3%A8che-RFVF.png', '2025-02-21 08:01:27', '2025-02-21 08:01:27', 5.00, 3, NULL, 0),
(16, 'Reptincel', 'reptincel', 'Reptincel est tiré du dinosaure ; il possède trois grandes et puissantes griffes acérées à chaque main et pied, qui l\'aident notamment à déchirer la peau de ses ennemis lors des combats. Sa peau est plus foncée que celle de sa pré-évolution et son museau s\'est allongé. Son crâne est désormais doté d\'une crête. Sa queue, longue et terminée par une flamme, lui sert notamment à élever sa température, le rendant plus puissant en combat, et à faire chuter ses adversaires avant de les achever.\r\n\r\nLe Pokédex le dit extrêmement agressif, violent, voire cruel dans certains cas. Il cherche toujours des adversaires plus puissants et sa flamme devient bleue tandis que sa température augmente.', 'https://www.pokepedia.fr/images/thumb/6/64/Reptincel-RFVF.png/500px-Reptincel-RFVF.png', '2025-02-21 08:01:52', '2025-02-21 08:01:52', 10.00, 3, NULL, 0),
(17, 'Dracaufeu', 'dracaufeu', 'Dracaufeu est basé sur un dragon européen. Contrairement à ses pré-évolutions, il a deux ailes lui permettant de voler : l\'intérieur des ailes est bleu alors que leur verso est orange. Son cou s\'est développé, il est désormais plus long et deux crêtes ont poussé à l\'arrière de son crâne. Ses membres supérieurs se sont atrophiés tandis que sa queue s\'est allongée pour permettre à ce titan de garder une certaine stabilité au sol bien qu\'il soit plus à l\'aise dans les airs. Sa dentition, avec ses canines apparentes, révèle une préférence marquée pour la viande ; il possède trois griffes à chaque patte et a le ventre jaune pâle. Pokémon noble, il n\'attaque pas les plus faibles que lui et cherche toujours des adversaires plus forts. Après un combat difficile ou s\'il est en colère, sa flamme s\'intensifie et devient blanc-bleu. Il crache des flammes pouvant faire fondre n\'importe quoi et est souvent la cause d\'incendies.\r\n\r\nDans les jeux vidéo, jusqu\'à la cinquième génération incluse, son cri était le même que celui de Rhinocorne.\r\n\r\nDans Pokémon Soleil et Lune, Pokémon Ultra-Soleil et Ultra-Lune et Pokémon : Let\'s Go, Pikachu et Let\'s Go, Évoli, Dracaufeu peut être utilisé en tant que Poké Monture.', 'https://www.pokepedia.fr/images/thumb/1/17/Dracaufeu-RFVF.png/500px-Dracaufeu-RFVF.png', '2025-02-21 08:02:45', '2025-02-21 08:02:45', 15.00, 3, NULL, 0),
(18, 'Méga-Dracaufeu X', 'méga-dracaufeu-x', 'Méga-Dracaufeu X ressemble à un Dracaufeu noir, au ventre bleu et aux yeux rouges. La membrane bleue foncé de ses ailes est acérée et une grande griffe termine chaque aile. Ses cornes sont plus effilées et le bout de celles-ci est bleu. Au niveau des épaules, des griffes bleues sortent de chaque côté (une vers l\'avant, une vers l\'arrière), et des pointes surgissent également à l\'arrière de son cou. La flamme au bout de sa queue est bleue, tout comme les deux jets de flammes qui lui sortent de chaque côté de la gueule. La couleur bleue des flammes indique une température extraordinairement élevée et extrêmement puissante.', 'https://www.pokepedia.fr/images/thumb/4/46/M%C3%A9ga-Dracaufeu_X-XY.png/500px-M%C3%A9ga-Dracaufeu_X-XY.png', '2025-02-21 08:03:49', '2025-02-21 08:03:49', 100.00, 3, NULL, 0),
(19, 'Méga-Dracaufeu Y', 'méga-dracaufeu-y', 'Méga-Dracaufeu Y a des allures plus bestiales que Dracaufeu. Trois cornes sont présentes sur sa tête, celle du milieu étant plus grande que les deux autres, et des pointes surgissent à l\'extrémité de sa queue. Il a une plus grande flamme et ses ailes à l\'apparence déchirée sont de grande envergure. Il a de petites ailes au niveau de ses bras et un aileron sur sa queue. Il devient digitigrade (il se déplace sur ses doigts).', 'https://www.pokepedia.fr/images/thumb/0/0b/M%C3%A9ga-Dracaufeu_Y-XY.png/500px-M%C3%A9ga-Dracaufeu_Y-XY.png', '2025-02-21 08:04:20', '2025-02-21 08:04:20', 100.00, 3, NULL, 0),
(20, 'Carapuce', 'carapuce', 'Carapuce est une petite tortue bipède de couleur bleue. Il possède une carapace brune au pourtour blanc, beige au niveau du ventre. Ses yeux sont grands et violacés. Il a une queue enroulée sur elle-même formant une spirale. Il possède quatre pattes avec chacune trois doigts.\r\n\r\nSa carapace, molle à la naissance, durcit avec le temps et lui sert à se protéger pour lancer ensuite des jets d\'eau ou d\'écume, mais aussi à améliorer son hydrodynamisme.', 'https://www.pokepedia.fr/images/thumb/c/cc/Carapuce-RFVF.png/500px-Carapuce-RFVF.png', '2025-02-21 08:04:53', '2025-02-21 08:04:53', 5.00, 3, NULL, 0),
(21, 'Carabaffe', 'carabaffe', 'Carabaffe est une tortue bipède de couleur bleu indigo, dont les oreilles et la queue sont recouvertes d\'une fourrure duveteuse de couleur blanche ; celle-ci fait d\'ailleurs penser à des vagues. Ses grands yeux sont marron. Trois griffes ornent chacune de ses pattes, et une épaisse carapace le protège des coups.\r\n\r\nCarabaffe est fait pour le combat : des canines affleurent sur ses lèvres, sa queue peut administrer de puissantes volées de coups et ses pattes robustes lui permettent de résister aux chocs. Il semble pouvoir nager extrêmement vite, notamment à l\'aide de ses oreilles et de sa queue.\r\n\r\nIl serait inspiré d\'un cryptide japonais : Minogame, avec lequel il partage les oreilles et la queue.', 'https://www.pokepedia.fr/images/thumb/2/2a/Carabaffe-RFVF.png/500px-Carabaffe-RFVF.png', '2025-02-21 08:05:23', '2025-02-21 08:05:23', 10.00, 3, NULL, 0),
(22, 'Tortank', 'tortank', 'Tortank est un bipède massif de la famille des tortues. Les extrémités supérieures gauche et droite de sa carapace sont ornées d\'un canon à eau pouvant être orienté dans diverses directions. Formé au combat, sa tête s\'est endurcie : le duvet de Carabaffe n\'est plus présent et il possède désormais deux petites oreilles et une queue courte. Ses griffes se sont maintenant développées sur tous ses doigts et ses yeux n\'ont pas changé de couleur.', 'https://www.pokepedia.fr/images/thumb/2/24/Tortank-RFVF.png/500px-Tortank-RFVF.png', '2025-02-21 08:05:49', '2025-02-21 08:05:49', 15.00, 3, NULL, 0),
(23, 'Tarsal', 'tarsal', 'Tarsal est un Pokémon humanoïde au corps principalement blanc. Il a des bras fins et des jambes qui s\'agrandissent au niveau de ses pieds. Une excroissance traîne derrière lui, qui crée l\'illusion qu\'il porte une robe de nuit trop grande. Sa tête est principalement cachée par une chevelure verte qui ressemble à une coupe au bol. Cependant, ses yeux rouge rosâtre sont parfois visibles. Il a deux cornes plates et rouges sur la tête, l\'une au dessus du front et l\'autre, plus petite, à l\'arrière.', 'https://www.pokepedia.fr/images/thumb/f/fe/Tarsal-RS.png/500px-Tarsal-RS.png', '2025-02-21 08:06:54', '2025-02-21 08:06:54', 10.00, 3, NULL, 0),
(24, 'Kirlia', 'kirlia', 'Kirlia ressemble à une danseuse de ballet. Ses cornes rouges se situent en hauteur sur les côtés de sa tête, positionnées comme des pinces tenant ses deux couettes. Ses cheveux verts forment également une longue frange en triangle qui masque le centre de son visage, mais qui laisse les yeux visibles. Le haut de son corps, blanc, prend la forme d\'un tutu de danse, le bas de son corps vert présente deux jambes longilignes. Le Pokémon se déplace sur la pointe des pieds en permanence.\r\n\r\nDans Légendes Pokémon : Arceus, ce Pokémon a par défaut un comportement défensif, puisqu\'il fuit aussitôt après sa première attaque contre le joueur.', 'https://www.pokepedia.fr/images/thumb/7/70/Kirlia-RS.png/500px-Kirlia-RS.png', '2025-02-21 08:07:45', '2025-02-21 08:07:45', 15.00, 3, NULL, 0),
(25, 'Gardevoir', 'gardevoir', 'Gardevoir est un Pokémon humanoïde bipède dont le corps ressemble à une robe flottante. Pratiquement tout son corps est de couleur blanche, sauf ses cheveux, ses bras et l\'intérieur de sa robe, qui sont verts. Ses cheveux bouclent sur son visage et sur les côtés de son visage. Il a derrière ses yeux rouges des épines blanches, qui font penser à un loup de carnaval. Il a de longs bras avec trois doigts à chaque main, et de minces jambes blanches. Une corne rouge qui ressemble à un aileron sort de sa poitrine, tandis qu\'une autre corne rouge, plus petite et plus arrondie, sort de son dos. Une bande verte qui fait le tour de sa taille relie la corne rouge à ses bras.', 'https://www.pokepedia.fr/images/thumb/3/30/Gardevoir-RS.png/500px-Gardevoir-RS.png', '2025-02-21 08:08:26', '2025-02-21 08:08:26', 20.00, 3, NULL, 0),
(26, 'Gallame', 'gallame', 'Gallame est un Pokémon bipède blanc. Son bassin est rond avec de larges jambes. Son torse est mince et vert avec des cornes rouges sortant de son poitrail et de son dos. Ses bras ont la forme de tonfas qui s\'étendent jusqu\'à ses coudes. Il utilise ses avant-bras comme des épées pour protéger les autres. Sa tête ressemble à un casque d\'hoplite ou de gladiateur avec un visage blanc et une crête bleu canard. Il a des piques sur les côtés de son visage. C\'est un maître dans l\'art de la courtoisie et de l\'escrime, qui est capable de prédire les mouvements de son adversaire.', 'https://www.pokepedia.fr/images/thumb/2/22/Gallame-DP.png/500px-Gallame-DP.png', '2025-02-21 08:09:10', '2025-02-21 08:09:10', 20.00, 3, NULL, 0),
(27, 'Évoli', 'Évoli', 'Évoli est un Pokémon mammalien quadrupède, entre canin et félin, avec une fourrure principalement brune. Le bout de sa queue broussailleuse et son gros col de fourrure sont de couleur crème. Il a de petites jambes minces avec trois petits orteils et un coussinet rose à chaque patte. Évoli a des yeux marron, de longues oreilles pointues et un petit nez noir. On trouve rarement ce Pokémon à l\'état sauvage, et plus souvent dans les villes et les zones urbaines. Cependant, on dit qu\'Évoli a une structure génétique irrégulière qui lui permet de s\'habituer à tous types d\'environnements. Évoli peut évoluer en huit formes différentes à ce jour, selon son environnement.', 'https://www.pokepedia.fr/images/thumb/8/8b/%C3%89voli-RFVF.png/500px-%C3%89voli-RFVF.png', '2025-02-21 08:10:10', '2025-02-21 08:10:10', 10.00, 3, NULL, 0),
(28, 'Aquali', 'aquali', 'Aquali est un Pokémon qui partage des traits physiques avec des animaux marins et terrestres. Il est quadrupède avec trois petits orteils à chaque pied et un coussinet bleu foncé sur ses pattes postérieures. Le corps d\'Aquali est bleu clair avec une marque bleu foncé sur la tête et une arête sur son dos. Il a une nageoire caudale qui, dans le passé, était confondue avec celle d\'une sirène. Il a une collerette blanche autour du cou, et trois nageoires beiges autour de la tête. Il est dit qu\'il pleuvra dans les heures qui suivent si les nageoires d\'Aquali commencent à vibrer.', 'https://www.pokepedia.fr/images/thumb/6/6b/Aquali-RFVF.png/500px-Aquali-RFVF.png', '2025-02-21 08:10:44', '2025-02-21 08:10:44', 25.00, 3, NULL, 0),
(29, 'Voltali', 'voltali', 'Voltali est un Pokémon quadrupède jaune avec de longues oreilles et une crinière blanche. Son pelage est hérissé et il possède une queue extrêmement courte, en comparaison avec les autres membres de sa famille évolutive.\r\n\r\nDans Légendes Pokémon : Arceus, ce Pokémon est trouvable à l\'état sauvage uniquement dans des Distorsions spatio-temporelles. Il a donc par défaut un comportement agressif et attaque le protagoniste dès qu\'il le voit.', 'https://www.pokepedia.fr/images/thumb/8/89/Voltali-RFVF.png/500px-Voltali-RFVF.png', '2025-02-21 08:11:12', '2025-02-21 08:11:12', 25.00, 3, NULL, 0),
(30, 'Pyroli', 'pyroli', 'Docile, calme, mais dangereux lorsqu\'il est énervé, ce Pokémon emmagasine de la chaleur dans son corps ce qui lui permet d\'envoyer par la suite un gigantesque torrent de flammes spectaculaires pouvant atteindre les 3 000 °C. Il incarne les flammes ardentes. Son corps est orangé avec de longues oreilles. Sa queue beige est touffue comme sa fourrure autour du cou et au-dessus de sa tête. Physiquement parlant, il est également la forme évoluée la plus proche d\'Évoli.', 'https://www.pokepedia.fr/images/thumb/6/64/Pyroli-RFVF.png/500px-Pyroli-RFVF.png', '2025-02-21 08:11:45', '2025-02-21 08:11:45', 25.00, 3, NULL, 0),
(31, 'Mentali', 'mentali', 'Mentali est un Pokémon mammalien quadrupède avec des jambes fines et des petites pattes. Il est recouvert de fine fourrure couleur lilas. Cette fourrure à la texture de velours est très sensible et permet au Mentali de sentir les changements de l\'air et donc de prédire la météo. Sa queue se divise en deux extrémités. Il a de grandes oreilles et des yeux violets aux pupilles blanches et lumineuses. Il a des touffes de fourrure près des yeux et une petite gemme rouge incrustée dans le front.', 'https://www.pokepedia.fr/images/thumb/c/cb/Mentali-RFVF.png/500px-Mentali-RFVF.png', '2025-02-21 08:12:35', '2025-02-21 08:12:35', 30.00, 3, NULL, 0),
(32, 'Noctali', 'noctali', 'Noctali a des pupilles fendues et un petit museau, ce qui le fait ressembler au chat noir. En règle générale, le chat noir est souvent considéré comme un présage de malchance et de mort, ce qui pourrait être l\'inspiration de son type Ténèbres. Cependant, au Japon, le chat noir est considéré comme chanceux, ce dont découle le nom japonais de Noctali : une combinaison de « black » (noir) et « lucky » (chanceux). De plus, les anneaux sur le corps de Noctali ressemblent aux marques et aux bijoux de Bastet, la déesse égyptienne des chats et de la lune. Les grandes oreilles pointues de Noctali sont inspirées du serval, un chat sauvage originaire d\'Afrique qui a de grandes oreilles et une queue touffue. Les servals peuvent parfois être noirs (bien que rares).', 'https://www.pokepedia.fr/images/thumb/7/70/Noctali-RFVF.png/500px-Noctali-RFVF.png', '2025-02-21 08:13:04', '2025-02-21 08:13:04', 30.00, 3, NULL, 0),
(33, 'Phyllali', 'phyllali', 'Phyllali est un Pokémon mammalien quadrupède, entre canin et félin, végétal. Son pelage, de couleur beige, porte quelques feuilles de couleur verte, comme ses oreilles, sa queue et la mèche de son front. L\'intérieur de ses oreilles et le bout de ses pattes sont de couleur marron. Ses yeux, de couleur noisette, font penser à ceux d\'une biche et ses oreilles pointues font penser aux oreilles des nymphes et des elfes. D\'un naturel calme, il utilise la photosynthèse pour purifier l\'air.', 'https://www.pokepedia.fr/images/thumb/f/ff/Phyllali-DEPS.png/500px-Phyllali-DEPS.png', '2025-02-21 08:13:50', '2025-02-21 08:13:50', 30.00, 3, NULL, 0),
(34, 'Givrali', 'givrali', 'Givrali est un Pokémon quadrupède ayant le corps bleu clair. Il possède sur le front une excroissance, qui est la base de deux autres, s\'apparentant à des cheveux et ressemblant à sa queue. Les extrémités de ses pattes sont bleu foncé, et il possède des marques en forme de losanges bleu foncé sur le dos, sur sa queue et sur ses excroissances. Il possède des yeux bleu foncé avec des pupilles blanches. Givrali est avantagé lors des tempêtes de neige, car il peut s\'y camoufler avec son talent Rideau Neige. Il peut contrôler la glace pour attaquer, en faisant apparaître des petits cristaux de glace flottant en suspension autour de lui.', 'https://www.pokepedia.fr/images/thumb/1/1b/Givrali-DEPS.png/500px-Givrali-DEPS.png', '2025-02-21 08:14:23', '2025-02-21 08:14:23', 30.00, 3, NULL, 0),
(35, 'Nymphali', 'nymphali', 'Nymphali est un Pokémon quadrupède à l\'allure mammalienne. Son corps est principalement recouvert d\'une fourrure blanche, à l\'exception de ses pattes, de ses oreilles et de sa queue qui sont roses. Il a de grands yeux bleus, de grandes oreilles dont la fourrure est plus épaisse et bleue à l\'intérieur, un minuscule museau ainsi que deux petites touffes de fourrure de chaque côté de la tête. Il a deux nœuds papillon sur le corps : un à la base de son oreille gauche et un autre sur le cou. Chaque nœud est blanc et rose au centre et possède une paire de rubans qui agissent comme des tentacules. Ces rubans sont blancs avec des extrémités bleues, avant lesquelles se trouvent une bande rose puis une bande bleu foncé. Il a des jambes sveltes avec des petites pattes à trois doigts et une queue légèrement recourbée et recouverte d\'une fourrure plus épaisse.', 'https://www.pokepedia.fr/images/thumb/8/83/Nymphali-XY.png/500px-Nymphali-XY.png', '2025-02-21 08:15:05', '2025-02-21 08:15:05', 30.00, 3, NULL, 0),
(36, 'Pichu', 'pichu', 'Pichu est un petit Pokémon souris de type Électrik. Il est jaune avec le bout des oreilles noir, ainsi que la queue et le cou. Ses joues sont rose foncé. Il encourage ses frères en leur donnant de toutes petites charges électriques grâce à sa queue.', 'https://www.pokepedia.fr/images/thumb/a/a0/Pichu-RFVF.png/500px-Pichu-RFVF.png', '2025-02-21 08:17:50', '2025-02-21 08:17:50', 2.00, 3, NULL, 0),
(37, 'Pikachu', 'pikachu', 'Pikachu est un petit Pokémon joufflu qui ressemble à un rongeur, au corps recouvert d\'un pelage jaune avec deux bandes horizontales brunes dans le dos. Il a une petite bouche, de longues oreilles pointues aux extrémités noires et des yeux noir et blanc. Il a sur chacune de ses joues un marquage rouge qui est en réalité une poche contenant de l\'électricité. Il a cinq petits doigts au bout de chacun de ses membres antérieurs, tandis que ses pattes postérieures ont trois petits orteils. Il a de la fourrure brune à la base de sa queue en forme d\'éclair. Bien que catégorisé comme quadrupède, il est capable de se tenir et de se déplacer sur ses pattes postérieures.', 'https://www.pokepedia.fr/images/thumb/7/76/Pikachu-DEPS.png/500px-Pikachu-DEPS.png', '2025-02-21 08:19:12', '2025-02-21 08:19:12', 10.00, 3, NULL, 0),
(38, 'Raichu', 'raichu', 'Raichu est un Pokémon ressemblant à une souris. Son corps est orange avec un ventre blanc. Il possède une longue queue noire avec au bout un éclair jaune. Ses joues sont jaunes, ses pattes sont marron ; il n\'a pas de doigt visible sur les pattes avant, mais en a trois les pattes arrière qui sont blanches côté paume tandis qu\'un cercle jaune fait penser à un talon. Ses oreilles sont grandes, jaunes à l\'intérieur et marron à l\'extérieur.\r\n\r\nDans Légendes Pokémon : Arceus, ce Pokémon est trouvable à l\'état sauvage uniquement sous sa forme Baron. Il a donc par défaut un comportement agressif et attaque le protagoniste dès qu\'il le voit.\r\n\r\nDepuis Pokémon Soleil et Lune, Raichu possède une nouvelle variante, appelée Raichu d\'Alola.', 'https://www.pokepedia.fr/images/thumb/7/7d/Raichu-RFVF.png/500px-Raichu-RFVF.png', '2025-02-21 08:19:55', '2025-02-21 08:19:55', 30.00, 3, NULL, 0),
(39, 'Raichu d\'Alola', 'raichu-d\'alola', 'À Alola, le corps de Raichu est d\'un orange plus vif et a une figure plus lisse et arrondie. Ses yeux sont bleus avec deux demi-lunes jaunes. Il a de grandes oreilles jaunes et marron en spirale. Ces oreilles sont plus épaisses et moins pointues que celles de ses cousins de la région de Kanto. En plus de son ventre couleur crème, Raichu d\'Alola a également des pattes blanches avec des lignes d\'un jaune vif entre les doigts. Les bandes de son dos sont devenues blanches et il a également un coussinet blanc sous chaque pied. L\'extrémité de sa queue, en forme d\'éclair, s\'est arrondie.', 'https://www.pokepedia.fr/images/thumb/7/79/Raichu_d%27Alola-SL.png/500px-Raichu_d%27Alola-SL.png', '2025-02-21 08:20:36', '2025-02-21 08:20:36', 35.00, 3, NULL, 0);

-- --------------------------------------------------------

--
-- Structure de la table `carts`
--

CREATE TABLE `carts` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `article_id` int NOT NULL,
  `quantite` int NOT NULL DEFAULT '1',
  `date_ajout` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

CREATE TABLE `commandes` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `date_transaction` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `montant_total` decimal(10,2) NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `ville` varchar(100) NOT NULL,
  `code_postal` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `commandes`
--

INSERT INTO `commandes` (`id`, `user_id`, `date_transaction`, `montant_total`, `adresse`, `ville`, `code_postal`) VALUES
(32, 3, '2025-02-21 08:25:48', 2.00, '9 rue du Professeur Tedenat', 'Montpellier', '34070');

-- --------------------------------------------------------

--
-- Structure de la table `commande_articles`
--

CREATE TABLE `commande_articles` (
  `id` int NOT NULL,
  `commande_id` int NOT NULL,
  `article_id` int NOT NULL,
  `quantite` int NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL,
  `article_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `commande_articles`
--

INSERT INTO `commande_articles` (`id`, `commande_id`, `article_id`, `quantite`, `prix_unitaire`, `article_name`) VALUES
(34, 32, 36, 1, 2.00, 'Pichu');

-- --------------------------------------------------------

--
-- Structure de la table `factures`
--

CREATE TABLE `factures` (
  `id` int NOT NULL,
  `commande_id` int NOT NULL,
  `nom_fichier` varchar(255) NOT NULL,
  `contenu` longblob,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `factures`
--

INSERT INTO `factures` (`id`, `commande_id`, `nom_fichier`, `contenu`, `date_creation`) VALUES
(16, 32, 'facture_32_2025-02-21.pdf', 0x255044462d312e370a25e2e3cfd30a362030206f626a0a3c3c202f54797065202f50616765202f506172656e74203120302052202f4c6173744d6f6469666965642028443a32303235303232313038323534382b30302730302729202f5265736f7572636573203220302052202f4d65646961426f78205b302e30303030303020302e303030303030203539352e323736303030203834312e3839303030305d202f43726f70426f78205b302e30303030303020302e303030303030203539352e323736303030203834312e3839303030305d202f426c656564426f78205b302e30303030303020302e303030303030203539352e323736303030203834312e3839303030305d202f5472696d426f78205b302e30303030303020302e303030303030203539352e323736303030203834312e3839303030305d202f417274426f78205b302e30303030303020302e303030303030203539352e323736303030203834312e3839303030305d202f436f6e74656e7473203720302052202f526f746174652030202f47726f7570203c3c202f54797065202f47726f7570202f53202f5472616e73706172656e6379202f4353202f446576696365524742203e3e202f416e6e6f7473205b203520302052205d202f505a2031203e3e0a656e646f626a0a372030206f626a0a3c3c2f46696c746572202f466c6174654465636f6465202f4c656e677468203736333e3e2073747265616d0a789ced9a4b53db301080effe153bd30b1c107a590f6e6d299d61a633507c030e6e6ca83b210163d7edad7fb7ffa2eb38214e6a5ce70171136bc613d9d64ada4fbbd266134a5c4db14006144ef1fa0697d7f811e0f511af5be79d0787270c18277454c0bb810f9e4397156493db0c784970f214e58951587445e573d1f38a5d385270c20dcfdbba56109b4b496042122565fef400272d6d5e200ee102561cd16983d2dc10e1d2399d25c5aae0dbaa7369a1155544eec6425b4d9430bba534539c582b774be9a94bef8ece659766a6d8c7c4b62b5d76e99d51baecd23ba374c9a55f47e74994f6243109efc6b77f5750aafeadf3e0309c080506dc456fd566a645efeeb9419df31959e512637543d99a78b46eb2a3457ba86b13df020e89c3c4d367198e983f9dae96c1404aab623e015ceec1fe3578a779b3f362c61c985a04f11c0bce8835b6924555cf1b65a1b5204ce9dc46472c4efc5e92a2b5be117c9eca9a0daf31b37fda1eba192e6943d94df3968a68d73ef13ef693108e508743ca0f39e52e5073c4dd12fb15213f8361a9156d27526555e1ce6cecced098dec42717a75025b4590a9a11c5a714de0671f8f8184210423ffa1efbd1e37000472febd32db50fd71256220380e7718a6452388b873739a534062f0cc2819f2c663aedd315b71735a3ab905453f8341c24f761bf1f85f19ab69675ee2b2f62736b17fa7fec4130c24c6e10f85d736cfb62bc2bc449d4eb873336b0fc04b71c23cb835a6eab389ea7fe208992df1dc846f64829915a54813c8ba31f900ea2c48fe2ce2c1bd194c2258ce92a9ade30f1fb9ba4d896b0b23b15ea4e05d78ea3043976c2a8f735ed9c6fc133619e22eb082e7818cc13cc7f7d845f1dc6054f81f6617c3da1521ee84052c2c46c8bdedd4b640066f34f02b704c9ab739f2d491394b67e2388cd73bc6e2962583d2fe074bedad0578d2c2236773b7db5e91f4de6041f9e5e2cd1a1a84e8a10334a8a60979c8f2a45ac33ccc2380ce0cb4ff0de9f1d9fc0d55e966524e9dd07376418df5eed4f16a3dea6ebf5c1b2b4ec1f1933434f0a656e6473747265616d0a656e646f626a0a312030206f626a0a3c3c202f54797065202f5061676573202f4b696473205b203620302052205d202f436f756e742031203e3e0a656e646f626a0a332030206f626a0a3c3c2f54797065202f466f6e74202f53756274797065202f5479706531202f42617365466f6e74202f48656c766574696361202f4e616d65202f4631202f456e636f64696e67202f57696e416e7369456e636f64696e67203e3e0a656e646f626a0a342030206f626a0a3c3c2f54797065202f466f6e74202f53756274797065202f5479706531202f42617365466f6e74202f48656c7665746963612d426f6c64202f4e616d65202f4632202f456e636f64696e67202f57696e416e7369456e636f64696e67203e3e0a656e646f626a0a322030206f626a0a3c3c202f50726f63536574205b2f504446202f54657874202f496d61676542202f496d61676543202f496d616765495d202f466f6e74203c3c202f4631203320302052202f4632203420302052203e3e202f584f626a656374203c3c203e3e203e3e0a656e646f626a0a352030206f626a0a3c3c2f54797065202f416e6e6f74202f53756274797065202f4c696e6b202f52656374205b322e38333530303020312e3030303030302031392e30303530303020322e3135363030305d202f50203620302052202f4e4d2028303030312d3030303029202f4d2028443a32303235303232313038323534382b30302730302729202f462034202f426f72646572205b30203020305d202f41203c3c2f53202f555249202f5552492028687474703a2f2f7777772e74637064662e6f7267293e3e202f48202f493e3e0a656e646f626a0a382030206f626a0a3c3c202f5469746c652028feff0046006100630074007500720065002000230033003229202f417574686f722028feff004d006f006e005300690074006529202f5375626a6563742028feff004600610063007400750072006500200064006500200063006f006d006d0061006e0064006529202f43726561746f722028feff0054004300500044004629202f50726f64756365722028feff0054004300500044004600200036002e0038002e00320020005c280068007400740070003a002f002f007700770077002e00740063007000640066002e006f00720067005c2929202f4372656174696f6e446174652028443a32303235303232313038323534382b30302730302729202f4d6f64446174652028443a32303235303232313038323534382b30302730302729202f54726170706564202f46616c7365203e3e0a656e646f626a0a392030206f626a0a3c3c202f54797065202f4d65746164617461202f53756274797065202f584d4c202f4c656e6774682034363832203e3e2073747265616d0a3c3f787061636b657420626567696e3d22efbbbf222069643d2257354d304d7043656869487a7265537a4e54637a6b633964223f3e0a3c783a786d706d65746120786d6c6e733a783d2261646f62653a6e733a6d6574612f2220783a786d70746b3d2241646f626520584d5020436f726520342e322e312d633034332035322e3337323732382c20323030392f30312f31382d31353a30383a3034223e0a093c7264663a52444620786d6c6e733a7264663d22687474703a2f2f7777772e77332e6f72672f313939392f30322f32322d7264662d73796e7461782d6e7323223e0a09093c7264663a4465736372697074696f6e207264663a61626f75743d222220786d6c6e733a64633d22687474703a2f2f7075726c2e6f72672f64632f656c656d656e74732f312e312f223e0a0909093c64633a666f726d61743e6170706c69636174696f6e2f7064663c2f64633a666f726d61743e0a0909093c64633a7469746c653e0a090909093c7264663a416c743e0a09090909093c7264663a6c6920786d6c3a6c616e673d22782d64656661756c74223e46616374757265202333323c2f7264663a6c693e0a090909093c2f7264663a416c743e0a0909093c2f64633a7469746c653e0a0909093c64633a63726561746f723e0a090909093c7264663a5365713e0a09090909093c7264663a6c693e4d6f6e536974653c2f7264663a6c693e0a090909093c2f7264663a5365713e0a0909093c2f64633a63726561746f723e0a0909093c64633a6465736372697074696f6e3e0a090909093c7264663a416c743e0a09090909093c7264663a6c6920786d6c3a6c616e673d22782d64656661756c74223e4661637475726520646520636f6d6d616e64653c2f7264663a6c693e0a090909093c2f7264663a416c743e0a0909093c2f64633a6465736372697074696f6e3e0a0909093c64633a7375626a6563743e0a090909093c7264663a4261673e0a09090909093c7264663a6c693e3c2f7264663a6c693e0a090909093c2f7264663a4261673e0a0909093c2f64633a7375626a6563743e0a09093c2f7264663a4465736372697074696f6e3e0a09093c7264663a4465736372697074696f6e207264663a61626f75743d222220786d6c6e733a786d703d22687474703a2f2f6e732e61646f62652e636f6d2f7861702f312e302f223e0a0909093c786d703a437265617465446174653e323032352d30322d32315430383a32353a34382b30303a30303c2f786d703a437265617465446174653e0a0909093c786d703a43726561746f72546f6f6c3e54435044463c2f786d703a43726561746f72546f6f6c3e0a0909093c786d703a4d6f64696679446174653e323032352d30322d32315430383a32353a34382b30303a30303c2f786d703a4d6f64696679446174653e0a0909093c786d703a4d65746164617461446174653e323032352d30322d32315430383a32353a34382b30303a30303c2f786d703a4d65746164617461446174653e0a09093c2f7264663a4465736372697074696f6e3e0a09093c7264663a4465736372697074696f6e207264663a61626f75743d222220786d6c6e733a7064663d22687474703a2f2f6e732e61646f62652e636f6d2f7064662f312e332f223e0a0909093c7064663a4b6579776f7264733e3c2f7064663a4b6579776f7264733e0a0909093c7064663a50726f64756365723e544350444620362e382e322028687474703a2f2f7777772e74637064662e6f7267293c2f7064663a50726f64756365723e0a09093c2f7264663a4465736372697074696f6e3e0a09093c7264663a4465736372697074696f6e207264663a61626f75743d222220786d6c6e733a786d704d4d3d22687474703a2f2f6e732e61646f62652e636f6d2f7861702f312e302f6d6d2f223e0a0909093c786d704d4d3a446f63756d656e7449443e757569643a30383835663564662d613163352d376534302d373930632d6635366466353135663434333c2f786d704d4d3a446f63756d656e7449443e0a0909093c786d704d4d3a496e7374616e636549443e757569643a30383835663564662d613163352d376534302d373930632d6635366466353135663434333c2f786d704d4d3a496e7374616e636549443e0a09093c2f7264663a4465736372697074696f6e3e0a09093c7264663a4465736372697074696f6e207264663a61626f75743d222220786d6c6e733a70646661457874656e73696f6e3d22687474703a2f2f7777772e6169696d2e6f72672f706466612f6e732f657874656e73696f6e2f2220786d6c6e733a70646661536368656d613d22687474703a2f2f7777772e6169696d2e6f72672f706466612f6e732f736368656d61232220786d6c6e733a7064666150726f70657274793d22687474703a2f2f7777772e6169696d2e6f72672f706466612f6e732f70726f706572747923223e0a0909093c70646661457874656e73696f6e3a736368656d61733e0a090909093c7264663a4261673e0a09090909093c7264663a6c69207264663a7061727365547970653d225265736f75726365223e0a0909090909093c70646661536368656d613a6e616d6573706163655552493e687474703a2f2f6e732e61646f62652e636f6d2f7064662f312e332f3c2f70646661536368656d613a6e616d6573706163655552493e0a0909090909093c70646661536368656d613a7072656669783e7064663c2f70646661536368656d613a7072656669783e0a0909090909093c70646661536368656d613a736368656d613e41646f62652050444620536368656d613c2f70646661536368656d613a736368656d613e0a0909090909093c70646661536368656d613a70726f70657274793e0a090909090909093c7264663a5365713e0a09090909090909093c7264663a6c69207264663a7061727365547970653d225265736f75726365223e0a0909090909090909093c7064666150726f70657274793a63617465676f72793e696e7465726e616c3c2f7064666150726f70657274793a63617465676f72793e0a0909090909090909093c7064666150726f70657274793a6465736372697074696f6e3e41646f62652050444620536368656d613c2f7064666150726f70657274793a6465736372697074696f6e3e0a0909090909090909093c7064666150726f70657274793a6e616d653e496e7374616e636549443c2f7064666150726f70657274793a6e616d653e0a0909090909090909093c7064666150726f70657274793a76616c7565547970653e5552493c2f7064666150726f70657274793a76616c7565547970653e0a09090909090909093c2f7264663a6c693e0a090909090909093c2f7264663a5365713e0a0909090909093c2f70646661536368656d613a70726f70657274793e0a09090909093c2f7264663a6c693e0a09090909093c7264663a6c69207264663a7061727365547970653d225265736f75726365223e0a0909090909093c70646661536368656d613a6e616d6573706163655552493e687474703a2f2f6e732e61646f62652e636f6d2f7861702f312e302f6d6d2f3c2f70646661536368656d613a6e616d6573706163655552493e0a0909090909093c70646661536368656d613a7072656669783e786d704d4d3c2f70646661536368656d613a7072656669783e0a0909090909093c70646661536368656d613a736368656d613e584d50204d65646961204d616e6167656d656e7420536368656d613c2f70646661536368656d613a736368656d613e0a0909090909093c70646661536368656d613a70726f70657274793e0a090909090909093c7264663a5365713e0a09090909090909093c7264663a6c69207264663a7061727365547970653d225265736f75726365223e0a0909090909090909093c7064666150726f70657274793a63617465676f72793e696e7465726e616c3c2f7064666150726f70657274793a63617465676f72793e0a0909090909090909093c7064666150726f70657274793a6465736372697074696f6e3e55554944206261736564206964656e74696669657220666f7220737065636966696320696e6361726e6174696f6e206f66206120646f63756d656e743c2f7064666150726f70657274793a6465736372697074696f6e3e0a0909090909090909093c7064666150726f70657274793a6e616d653e496e7374616e636549443c2f7064666150726f70657274793a6e616d653e0a0909090909090909093c7064666150726f70657274793a76616c7565547970653e5552493c2f7064666150726f70657274793a76616c7565547970653e0a09090909090909093c2f7264663a6c693e0a090909090909093c2f7264663a5365713e0a0909090909093c2f70646661536368656d613a70726f70657274793e0a09090909093c2f7264663a6c693e0a09090909093c7264663a6c69207264663a7061727365547970653d225265736f75726365223e0a0909090909093c70646661536368656d613a6e616d6573706163655552493e687474703a2f2f7777772e6169696d2e6f72672f706466612f6e732f69642f3c2f70646661536368656d613a6e616d6573706163655552493e0a0909090909093c70646661536368656d613a7072656669783e7064666169643c2f70646661536368656d613a7072656669783e0a0909090909093c70646661536368656d613a736368656d613e5044462f4120494420536368656d613c2f70646661536368656d613a736368656d613e0a0909090909093c70646661536368656d613a70726f70657274793e0a090909090909093c7264663a5365713e0a09090909090909093c7264663a6c69207264663a7061727365547970653d225265736f75726365223e0a0909090909090909093c7064666150726f70657274793a63617465676f72793e696e7465726e616c3c2f7064666150726f70657274793a63617465676f72793e0a0909090909090909093c7064666150726f70657274793a6465736372697074696f6e3e50617274206f66205044462f41207374616e646172643c2f7064666150726f70657274793a6465736372697074696f6e3e0a0909090909090909093c7064666150726f70657274793a6e616d653e706172743c2f7064666150726f70657274793a6e616d653e0a0909090909090909093c7064666150726f70657274793a76616c7565547970653e496e74656765723c2f7064666150726f70657274793a76616c7565547970653e0a09090909090909093c2f7264663a6c693e0a09090909090909093c7264663a6c69207264663a7061727365547970653d225265736f75726365223e0a0909090909090909093c7064666150726f70657274793a63617465676f72793e696e7465726e616c3c2f7064666150726f70657274793a63617465676f72793e0a0909090909090909093c7064666150726f70657274793a6465736372697074696f6e3e416d656e646d656e74206f66205044462f41207374616e646172643c2f7064666150726f70657274793a6465736372697074696f6e3e0a0909090909090909093c7064666150726f70657274793a6e616d653e616d643c2f7064666150726f70657274793a6e616d653e0a0909090909090909093c7064666150726f70657274793a76616c7565547970653e546578743c2f7064666150726f70657274793a76616c7565547970653e0a09090909090909093c2f7264663a6c693e0a09090909090909093c7264663a6c69207264663a7061727365547970653d225265736f75726365223e0a0909090909090909093c7064666150726f70657274793a63617465676f72793e696e7465726e616c3c2f7064666150726f70657274793a63617465676f72793e0a0909090909090909093c7064666150726f70657274793a6465736372697074696f6e3e436f6e666f726d616e6365206c6576656c206f66205044462f41207374616e646172643c2f7064666150726f70657274793a6465736372697074696f6e3e0a0909090909090909093c7064666150726f70657274793a6e616d653e636f6e666f726d616e63653c2f7064666150726f70657274793a6e616d653e0a0909090909090909093c7064666150726f70657274793a76616c7565547970653e546578743c2f7064666150726f70657274793a76616c7565547970653e0a09090909090909093c2f7264663a6c693e0a090909090909093c2f7264663a5365713e0a0909090909093c2f70646661536368656d613a70726f70657274793e0a09090909093c2f7264663a6c693e0a090909093c2f7264663a4261673e0a0909093c2f70646661457874656e73696f6e3a736368656d61733e0a09093c2f7264663a4465736372697074696f6e3e0a093c2f7264663a5244463e0a3c2f783a786d706d6574613e0a3c3f787061636b657420656e643d2277223f3e0a656e6473747265616d0a656e646f626a0a31302030206f626a0a3c3c202f54797065202f436174616c6f67202f56657273696f6e202f312e37202f5061676573203120302052202f4e616d6573203c3c203e3e202f566965776572507265666572656e636573203c3c202f446972656374696f6e202f4c3252203e3e202f506167654c61796f7574202f53696e676c6550616765202f506167654d6f6465202f5573654e6f6e65202f4f70656e416374696f6e205b3620302052202f46697448206e756c6c5d202f4d65746164617461203920302052203e3e0a656e646f626a0a787265660a302031310a303030303030303030302036353533352066200a30303030303031333136203030303030206e200a30303030303031353932203030303030206e200a30303030303031333735203030303030206e200a30303030303031343831203030303030206e200a30303030303031373036203030303030206e200a30303030303030303135203030303030206e200a30303030303030343833203030303030206e200a30303030303031393232203030303030206e200a30303030303032323536203030303030206e200a30303030303037303230203030303030206e200a747261696c65720a3c3c202f53697a65203131202f526f6f7420313020302052202f496e666f203820302052202f4944205b203c30383835663564666131633537653430373930636635366466353135663434333e203c30383835663564666131633537653430373930636635366466353135663434333e205d203e3e0a7374617274787265660a373232380a2525454f460a, '2025-02-21 08:25:48');

-- --------------------------------------------------------

--
-- Structure de la table `notes_articles`
--

CREATE TABLE `notes_articles` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `article_id` int NOT NULL,
  `note` int NOT NULL,
  `commentaire` text,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Déchargement des données de la table `notes_articles`
--

INSERT INTO `notes_articles` (`id`, `user_id`, `article_id`, `note`, `commentaire`, `date_creation`) VALUES
(3, 3, 36, 5, 'Top', '2025-02-21 08:26:07');

-- --------------------------------------------------------

--
-- Structure de la table `stocks`
--

CREATE TABLE `stocks` (
  `id` int NOT NULL,
  `article_id` int NOT NULL,
  `quantite` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `stocks`
--

INSERT INTO `stocks` (`id`, `article_id`, `quantite`) VALUES
(18, 12, 5),
(19, 13, 10),
(20, 14, 15),
(21, 15, 5),
(22, 16, 10),
(23, 17, 15),
(24, 18, 100),
(25, 19, 100),
(26, 20, 5),
(27, 21, 10),
(28, 22, 15),
(29, 23, 10),
(30, 24, 15),
(31, 25, 20),
(32, 26, 20),
(33, 27, 10),
(34, 28, 25),
(35, 29, 25),
(36, 30, 25),
(37, 31, 30),
(38, 32, 30),
(39, 33, 30),
(40, 34, 30),
(41, 35, 30),
(42, 36, 1),
(43, 37, 10),
(44, 38, 30),
(45, 39, 35);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `solde` decimal(10,2) DEFAULT '0.00',
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `solde`, `avatar`, `role`, `created_at`) VALUES
(1, 'Akuox', 'loic.cano@ynov.com', '$2y$10$YwMjlPqW6KupIu3zYkpa8O0zQC3UdMwB/Mm2tRYZSjrcj/I/nxDJC', 82109274.09, 'uploads/avatars/1_avatar.png', 'admin', '2025-02-18 09:24:59'),
(3, 'MrLandy', 'moi@gmail.com', '$2y$10$EfI5QiZMn9aBXYcNl4p/AeDWXaOfQYvvl4.stdrVmsaiNZK23iVb2', 99978.00, 'uploads/avatars/3_avatar.jpg', 'user', '2025-02-18 11:55:10');

-- --------------------------------------------------------

--
-- Structure de la table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `article_id` int NOT NULL,
  `date_ajout` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `commande_articles`
--
ALTER TABLE `commande_articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commande_id` (`commande_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `factures`
--
ALTER TABLE `factures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commande_id` (`commande_id`);

--
-- Index pour la table `notes_articles`
--
ALTER TABLE `notes_articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_article` (`user_id`,`article_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist` (`user_id`,`article_id`),
  ADD KEY `article_id` (`article_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT pour la table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT pour la table `commandes`
--
ALTER TABLE `commandes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT pour la table `commande_articles`
--
ALTER TABLE `commande_articles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT pour la table `factures`
--
ALTER TABLE `factures`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `notes_articles`
--
ALTER TABLE `notes_articles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `carts_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`);

--
-- Contraintes pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD CONSTRAINT `commandes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `commande_articles`
--
ALTER TABLE `commande_articles`
  ADD CONSTRAINT `commande_articles_ibfk_1` FOREIGN KEY (`commande_id`) REFERENCES `commandes` (`id`),
  ADD CONSTRAINT `commande_articles_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`);

--
-- Contraintes pour la table `factures`
--
ALTER TABLE `factures`
  ADD CONSTRAINT `factures_ibfk_1` FOREIGN KEY (`commande_id`) REFERENCES `commandes` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notes_articles`
--
ALTER TABLE `notes_articles`
  ADD CONSTRAINT `notes_articles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `notes_articles_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`);

--
-- Contraintes pour la table `stocks`
--
ALTER TABLE `stocks`
  ADD CONSTRAINT `stocks_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`);

--
-- Contraintes pour la table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
