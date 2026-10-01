const authView = document.querySelector('#auth-view');
const appView = document.querySelector('#app-view');
const authForm = document.querySelector('#auth-form');
const authMessage = document.querySelector('#auth-message');
const gameMessage = document.querySelector('#game-message');
const boardButtons = [...document.querySelectorAll('#board button')];
const checkersBoard = document.querySelector('#checkers-board');
let checkersButtons = [];
let selectedCheckersFrom = null;
let currentGame = null;

const translations = {
    en: {
        tagline: 'THE CLASSIC, HEAD TO HEAD', language: 'Language', account_action: 'Account action', home: 'Gridlock home', versus: 'vs.',
        choose_game: 'Choose game', tic_tac_toe: 'Tic-tac-toe', checkers: 'Checkers',
        checkers_board_label: 'Checkers board', selected_piece: 'Piece selected. Choose a highlighted square.',
        mandatory_capture: 'A capture is available and must be taken.',
        continue_capture: 'Continue capturing with the same piece.',
        checkers_instructions: 'Your pieces are {color}. Capture all opposing pieces or block every legal move to win.',
        checkers_share_code: 'Your pieces are {color}. Share the code so your friend can play with the other color.',
        checkers_opponent_thinking: 'Your pieces are {color}. {opponent} is thinking…',
        checkers_ai_game_started: 'Checkers started. You play {yourColor}; Gridlock AI plays {opponentColor} ({difficulty}).',
        checkers_pieces_x: 'lime green pieces', checkers_pieces_o: 'blue pieces',
        checkers_color_x: 'lime green', checkers_color_o: 'blue',
        checkers_rules_title: 'How to play Checkers',
        checkers_rule_move: 'Men move one diagonal square forward; kings move one diagonal square in either direction.',
        checkers_rule_capture: 'Jump an adjacent opponent to capture it. Captures are mandatory.',
        checkers_rule_chain: 'If another capture is available after a jump, continue with the same piece.',
        checkers_rule_king: 'A man reaching the far edge becomes a king.',
        checkers_rule_win: 'Capture all opposing pieces or leave your opponent with no legal moves to win.',
        play_checkers_ai: 'Play Checkers against AI',
        row: 'Row', column: 'column', ai_opponent: 'Gridlock AI',
        auth_eyebrow: 'A LITTLE FRIENDLY COMPETITION', welcome_title: 'Make your<br>next move.',
        intro_copy: 'Start a game, share your code, and see who gets three in a row. Your record follows you from game to game.',
        sign_in: 'Sign in', create_account: 'Create account', username: 'Username',
        username_hint: '3–20 letters, numbers, or underscores', password: 'Password',
        password_hint: 'At least 10 characters', your_game_room: 'YOUR GAME ROOM',
        welcome_back: 'Welcome back,', sign_out: 'Sign out', play_now: 'PLAY NOW',
        game_room: 'Game room', no_game: 'No game selected', start_game: 'Start a game',
        ai_difficulty: 'AI difficulty', easy: 'Easy — mostly random', medium: 'Medium — tactical',
        hard: 'Hard — unbeatable', play_ai: 'Play against AI', game_code: 'Game code',
        enter_code: 'ENTER CODE', join_game: 'Join game', game_code_caps: 'GAME CODE',
        copy: 'Copy', opponent: 'OPPONENT', your_games: 'Your games',
        games_empty: 'Your games will show up here.', score_so_far: 'THE SCORE SO FAR',
        your_stats: 'Your stats', played: 'Played', wins: 'Wins', losses: 'Losses',
        draws: 'Draws', top_players: 'TOP PLAYERS', leaderboard: 'Leaderboard',
        footer_tagline: 'Good games are just one move away.', waiting: 'Waiting for opponent',
        game_over: 'Game over', in_progress: 'In progress', aborted: 'Aborted', single_player: 'single player',
        abort_game: 'Abort game', confirm_abort_game: 'Abort this game? This cannot be undone.',
        game_aborted: 'This game was aborted.',
        waiting_player: 'Waiting for a player', share_code: 'Share this code with a friend to play as O. You are X.',
        your_turn_x: 'Your turn — you are X.', your_turn: 'Your turn — you are {symbol}.',
        opponent_thinking: '{opponent} is thinking…', you_win: 'You win!',
        player_wins: '{player} wins!', draw_result: 'It’s a draw!',
        games_empty_leaderboard: 'Play a game to appear on the leaderboard.',
        game_created: 'Game created. Share the code to invite a player.',
        ai_game_started: 'Game started. You are X; Gridlock AI is O ({difficulty}).',
        joined_game: 'You joined the game. Good luck!', copied: 'Copied!',
        copy_failed: 'Could not copy the code. Select it to copy manually.',
        connection_error: 'Could not connect to the game server. Check your PHP and database configuration.',
        invalid_response: 'The server returned an invalid response (HTTP {status}, {type}). Check the PHP server logs and confirm api.php is being served by PHP.',
        error_login: 'Incorrect username or password.', error_username_taken: 'That username is already taken.',
        error_auth_required: 'Please sign in to continue.', error_session: 'Your session has expired. Refresh the page and try again.',
        error_invalid_username_password: 'Enter a valid username and password.',
        error_registration: 'Use a 3–20 character username (letters, numbers, underscores) and a password of 10–72 characters.',
        error_game_unavailable: 'That game is unavailable. Check the code or start a new game.',
        error_own_game: 'You cannot join your own game.', error_turn: 'It is not your turn.',
        error_wait_ai: 'Wait for the AI to make its move.', error_square: 'That square is already taken.',
        error_not_accepting: 'This game is not accepting moves.', error_game_not_found: 'Game not found.',
        error_invalid_code: 'Enter a valid 6-character game code.', error_invalid_move: 'Invalid move.',
        error_invalid_game: 'Invalid game.', error_ai_difficulty: 'Choose a valid AI difficulty.',
        error_generic: 'Something went wrong. Please try again.', wins_short: 'W',
        error_invalid_game_kind: 'Choose a valid game.',
        error_unknown_action: 'The server does not support this action. Deploy the latest api.php and apply its required database migration.',
        board_label: 'Tic-tac-toe board', top_left: 'Top left', top_middle: 'Top middle',
        top_right: 'Top right', middle_left: 'Middle left', center: 'Center',
        middle_right: 'Middle right', bottom_left: 'Bottom left', bottom_middle: 'Bottom middle',
        bottom_right: 'Bottom right',
    },
    de: {
        tagline: 'DER KLASSIKER IM DUELL', language: 'Sprache', account_action: 'Kontoaktion', home: 'Gridlock Startseite', versus: 'gegen',
        choose_game: 'Spiel auswählen', tic_tac_toe: 'Tic-Tac-Toe', checkers: 'Dame',
        checkers_board_label: 'Dame-Spielbrett', selected_piece: 'Figur ausgewählt. Wähle ein markiertes Feld.',
        mandatory_capture: 'Ein Schlagzug ist möglich und muss ausgeführt werden.',
        continue_capture: 'Schlage mit derselben Figur weiter.',
        checkers_instructions: 'Deine Steine sind {color}. Gewinne, indem du alle gegnerischen Figuren schlägst oder alle Züge blockierst.',
        checkers_share_code: 'Deine Steine sind {color}. Teile den Code, damit dein Mitspieler die andere Farbe übernehmen kann.',
        checkers_opponent_thinking: 'Deine Steine sind {color}. {opponent} ist am Zug …',
        checkers_ai_game_started: 'Dame gestartet. Du spielst mit den {yourColor}; Gridlock KI spielt mit den {opponentColor} ({difficulty}).',
        checkers_pieces_x: 'limettengrünen Steinen', checkers_pieces_o: 'blauen Steinen',
        checkers_color_x: 'limettengrün', checkers_color_o: 'blau',
        checkers_rules_title: 'So wird Dame gespielt',
        checkers_rule_move: 'Steine ziehen ein Feld diagonal vorwärts; Damen ziehen ein Feld diagonal vorwärts oder rückwärts.',
        checkers_rule_capture: 'Überspringe einen benachbarten gegnerischen Stein, um ihn zu schlagen. Schlagzüge sind Pflicht.',
        checkers_rule_chain: 'Ist nach einem Sprung ein weiterer Schlag möglich, musst du mit demselben Stein weiterschlagen.',
        checkers_rule_king: 'Erreicht ein Stein die gegnerische Grundreihe, wird er zur Dame.',
        checkers_rule_win: 'Gewinne, indem du alle gegnerischen Steine schlägst oder alle möglichen Züge blockierst.',
        play_checkers_ai: 'Dame gegen KI spielen',
        row: 'Reihe', column: 'Spalte', ai_opponent: 'Gridlock KI',
        auth_eyebrow: 'EIN KLEINER WETTKAMPF UNTER FREUNDEN', welcome_title: 'Setze<br>deinen Zug.',
        intro_copy: 'Starte ein Spiel, teile deinen Code und finde heraus, wer zuerst drei Zeichen in einer Reihe hat. Deine Statistik bleibt erhalten.',
        sign_in: 'Anmelden', create_account: 'Konto erstellen', username: 'Benutzername',
        username_hint: '3–20 Buchstaben, Zahlen oder Unterstriche', password: 'Passwort',
        password_hint: 'Mindestens 10 Zeichen', your_game_room: 'DEIN SPIELBEREICH',
        welcome_back: 'Willkommen zurück,', sign_out: 'Abmelden', play_now: 'JETZT SPIELEN',
        game_room: 'Spielbereich', no_game: 'Kein Spiel ausgewählt', start_game: 'Spiel starten',
        ai_difficulty: 'KI-Schwierigkeit', easy: 'Leicht — meist zufällig', medium: 'Mittel — taktisch',
        hard: 'Schwer — unschlagbar', play_ai: 'Gegen die KI spielen', game_code: 'Spielcode',
        enter_code: 'CODE EINGEBEN', join_game: 'Spiel beitreten', game_code_caps: 'SPIELCODE',
        copy: 'Kopieren', opponent: 'GEGNER', your_games: 'Deine Spiele',
        games_empty: 'Deine Spiele erscheinen hier.', score_so_far: 'DEIN BISHERIGER STAND',
        your_stats: 'Deine Statistik', played: 'Gespielt', wins: 'Siege', losses: 'Niederlagen',
        draws: 'Unentschieden', top_players: 'TOPSPIELER', leaderboard: 'Bestenliste',
        footer_tagline: 'Ein gutes Spiel ist nur einen Zug entfernt.', waiting: 'Warte auf Mitspieler',
        game_over: 'Spiel beendet', in_progress: 'Läuft', aborted: 'Abgebrochen', single_player: 'Einzelspieler',
        abort_game: 'Spiel abbrechen', confirm_abort_game: 'Möchtest du dieses Spiel wirklich abbrechen? Das kann nicht rückgängig gemacht werden.',
        game_aborted: 'Dieses Spiel wurde abgebrochen.',
        waiting_player: 'Warte auf Mitspieler', share_code: 'Teile diesen Code mit einem Freund. Du spielst X, dein Gegenüber O.',
        your_turn_x: 'Du bist dran — du spielst X.', your_turn: 'Du bist dran — du spielst {symbol}.',
        opponent_thinking: '{opponent} ist am Zug …', you_win: 'Du gewinnst!',
        player_wins: '{player} gewinnt!', draw_result: 'Unentschieden!',
        games_empty_leaderboard: 'Spiele eine Runde, um in der Bestenliste zu erscheinen.',
        game_created: 'Spiel erstellt. Teile den Code, um jemanden einzuladen.',
        ai_game_started: 'Spiel gestartet. Du bist X, Gridlock KI ist O ({difficulty}).',
        joined_game: 'Du bist dem Spiel beigetreten. Viel Glück!', copied: 'Kopiert!',
        copy_failed: 'Code konnte nicht kopiert werden. Bitte markiere ihn und kopiere ihn manuell.',
        connection_error: 'Keine Verbindung zum Spielserver. Prüfe die PHP- und Datenbankkonfiguration.',
        invalid_response: 'Der Server hat eine ungültige Antwort gesendet (HTTP {status}, {type}). Prüfe das PHP-Fehlerprotokoll und ob api.php von PHP ausgeführt wird.',
        error_login: 'Benutzername oder Passwort ist falsch.', error_username_taken: 'Dieser Benutzername ist bereits vergeben.',
        error_auth_required: 'Bitte melde dich an, um fortzufahren.', error_session: 'Deine Sitzung ist abgelaufen. Lade die Seite neu und versuche es erneut.',
        error_invalid_username_password: 'Gib einen gültigen Benutzernamen und ein Passwort ein.',
        error_registration: 'Verwende einen Benutzernamen mit 3–20 Zeichen (Buchstaben, Zahlen, Unterstriche) und ein Passwort mit 10–72 Zeichen.',
        error_game_unavailable: 'Dieses Spiel ist nicht verfügbar. Prüfe den Code oder starte ein neues Spiel.',
        error_own_game: 'Du kannst deinem eigenen Spiel nicht beitreten.', error_turn: 'Du bist nicht am Zug.',
        error_wait_ai: 'Warte, bis die KI ihren Zug gemacht hat.', error_square: 'Dieses Feld ist bereits belegt.',
        error_not_accepting: 'Dieses Spiel nimmt keine Züge mehr an.', error_game_not_found: 'Spiel nicht gefunden.',
        error_invalid_code: 'Gib einen gültigen Spielcode mit 6 Zeichen ein.', error_invalid_move: 'Ungültiger Zug.',
        error_invalid_game: 'Ungültiges Spiel.', error_ai_difficulty: 'Wähle eine gültige KI-Schwierigkeit.',
        error_generic: 'Etwas ist schiefgelaufen. Bitte versuche es erneut.', wins_short: 'S',
        error_invalid_game_kind: 'Wähle ein gültiges Spiel aus.',
        error_unknown_action: 'Der Server unterstützt diese Aktion nicht. Installiere die aktuelle api.php und führe die erforderliche Datenbankmigration aus.',
        board_label: 'Tic-Tac-Toe-Spielbrett', top_left: 'Oben links', top_middle: 'Oben Mitte',
        top_right: 'Oben rechts', middle_left: 'Mitte links', center: 'Mitte',
        middle_right: 'Mitte rechts', bottom_left: 'Unten links', bottom_middle: 'Unten Mitte',
        bottom_right: 'Unten rechts',
    },
    fr: {
        tagline: 'LE CLASSIQUE, EN DUEL', language: 'Langue', account_action: 'Action du compte', home: 'Accueil Gridlock', versus: 'contre',
        choose_game: 'Choisir un jeu', tic_tac_toe: 'Morpion', checkers: 'Dames',
        checkers_board_label: 'Plateau de dames', selected_piece: 'Pion sélectionné. Choisissez une case en surbrillance.',
        mandatory_capture: 'Une prise est possible et doit être effectuée.',
        continue_capture: 'Continuez la prise avec le même pion.',
        checkers_instructions: 'Vos pions sont {color}. Capturez tous les pions adverses ou bloquez tous leurs coups pour gagner.',
        checkers_share_code: 'Vos pions sont {color}. Partagez le code pour que votre ami joue avec l’autre couleur.',
        checkers_opponent_thinking: 'Vos pions sont {color}. Au tour de {opponent}…',
        checkers_ai_game_started: 'Partie de dames lancée. Vous jouez avec les {yourColor} ; Gridlock IA joue avec les {opponentColor} ({difficulty}).',
        checkers_pieces_x: 'pions vert citron', checkers_pieces_o: 'pions bleus',
        checkers_color_x: 'vert citron', checkers_color_o: 'bleus',
        checkers_rules_title: 'Comment jouer aux dames',
        checkers_rule_move: 'Les pions avancent d’une case en diagonale ; les dames se déplacent d’une case en diagonale dans les deux sens.',
        checkers_rule_capture: 'Sautez par-dessus un pion adverse adjacent pour le capturer. Les prises sont obligatoires.',
        checkers_rule_chain: 'Si une autre prise est possible après un saut, continuez avec le même pion.',
        checkers_rule_king: 'Un pion qui atteint le bord opposé devient une dame.',
        checkers_rule_win: 'Capturez tous les pions adverses ou bloquez tous leurs coups pour gagner.',
        play_checkers_ai: 'Jouer aux dames contre l’IA',
        row: 'Rangée', column: 'colonne', ai_opponent: 'Gridlock IA',
        auth_eyebrow: 'UN PETIT DÉFI ENTRE AMIS', welcome_title: 'À toi<br>de jouer.',
        intro_copy: 'Lancez une partie, partagez votre code et découvrez qui alignera trois symboles. Vos statistiques vous suivent de partie en partie.',
        sign_in: 'Connexion', create_account: 'Créer un compte', username: 'Nom d’utilisateur',
        username_hint: '3 à 20 lettres, chiffres ou tirets bas', password: 'Mot de passe',
        password_hint: '10 caractères minimum', your_game_room: 'VOTRE ESPACE DE JEU',
        welcome_back: 'Bon retour,', sign_out: 'Déconnexion', play_now: 'JOUER',
        game_room: 'Espace de jeu', no_game: 'Aucune partie sélectionnée', start_game: 'Lancer une partie',
        ai_difficulty: 'Difficulté de l’IA', easy: 'Facile — surtout aléatoire', medium: 'Moyen — tactique',
        hard: 'Difficile — imbattable', play_ai: 'Jouer contre l’IA', game_code: 'Code de partie',
        enter_code: 'SAISIR LE CODE', join_game: 'Rejoindre', game_code_caps: 'CODE DE PARTIE',
        copy: 'Copier', opponent: 'ADVERSAIRE', your_games: 'Vos parties',
        games_empty: 'Vos parties apparaîtront ici.', score_so_far: 'VOTRE PALMARÈS',
        your_stats: 'Vos statistiques', played: 'Jouées', wins: 'Victoires', losses: 'Défaites',
        draws: 'Nuls', top_players: 'MEILLEURS JOUEURS', leaderboard: 'Classement',
        footer_tagline: 'Une belle partie n’est qu’à un coup de commencer.', waiting: 'En attente d’un adversaire',
        game_over: 'Partie terminée', in_progress: 'En cours', aborted: 'Abandonnée', single_player: 'solo',
        abort_game: 'Abandonner la partie', confirm_abort_game: 'Voulez-vous vraiment abandonner cette partie ? Cette action est irréversible.',
        game_aborted: 'Cette partie a été abandonnée.',
        waiting_player: 'En attente d’un joueur', share_code: 'Partagez ce code avec un ami. Vous jouez X, votre adversaire joue O.',
        your_turn_x: 'À vous de jouer — vous êtes X.', your_turn: 'À vous de jouer — vous êtes {symbol}.',
        opponent_thinking: 'Au tour de {opponent}…', you_win: 'Vous avez gagné !',
        player_wins: '{player} a gagné !', draw_result: 'Match nul !',
        games_empty_leaderboard: 'Jouez une partie pour apparaître au classement.',
        game_created: 'Partie créée. Partagez le code pour inviter un joueur.',
        ai_game_started: 'Partie lancée. Vous êtes X, Gridlock IA est O ({difficulty}).',
        joined_game: 'Vous avez rejoint la partie. Bonne chance !', copied: 'Copié !',
        copy_failed: 'Impossible de copier le code. Sélectionnez-le pour le copier manuellement.',
        connection_error: 'Connexion au serveur impossible. Vérifiez la configuration PHP et la base de données.',
        invalid_response: 'Le serveur a renvoyé une réponse invalide (HTTP {status}, {type}). Vérifiez les journaux PHP et que api.php est exécuté par PHP.',
        error_login: 'Nom d’utilisateur ou mot de passe incorrect.', error_username_taken: 'Ce nom d’utilisateur est déjà utilisé.',
        error_auth_required: 'Connectez-vous pour continuer.', error_session: 'Votre session a expiré. Rechargez la page et réessayez.',
        error_invalid_username_password: 'Saisissez un nom d’utilisateur et un mot de passe valides.',
        error_registration: 'Utilisez un nom de 3 à 20 caractères (lettres, chiffres, tirets bas) et un mot de passe de 10 à 72 caractères.',
        error_game_unavailable: 'Cette partie est indisponible. Vérifiez le code ou lancez une nouvelle partie.',
        error_own_game: 'Vous ne pouvez pas rejoindre votre propre partie.', error_turn: 'Ce n’est pas à vous de jouer.',
        error_wait_ai: 'Attendez que l’IA joue.', error_square: 'Cette case est déjà occupée.',
        error_not_accepting: 'Cette partie n’accepte plus de coups.', error_game_not_found: 'Partie introuvable.',
        error_invalid_code: 'Saisissez un code de partie valide de 6 caractères.', error_invalid_move: 'Coup invalide.',
        error_invalid_game: 'Partie invalide.', error_ai_difficulty: 'Choisissez une difficulté d’IA valide.',
        error_generic: 'Une erreur est survenue. Veuillez réessayer.', wins_short: 'V',
        error_invalid_game_kind: 'Choisissez un jeu valide.',
        error_unknown_action: 'Le serveur ne prend pas en charge cette action. Déployez la dernière version de api.php et appliquez la migration de base de données requise.',
        board_label: 'Plateau de morpion', top_left: 'En haut à gauche', top_middle: 'En haut au centre',
        top_right: 'En haut à droite', middle_left: 'Au milieu à gauche', center: 'Au centre',
        middle_right: 'Au milieu à droite', bottom_left: 'En bas à gauche', bottom_middle: 'En bas au centre',
        bottom_right: 'En bas à droite',
    },
    es: {
        tagline: 'EL CLÁSICO, CARA A CARA', language: 'Idioma', account_action: 'Acción de cuenta', home: 'Inicio de Gridlock', versus: 'contra',
        choose_game: 'Elegir juego', tic_tac_toe: 'Tres en raya', checkers: 'Damas',
        checkers_board_label: 'Tablero de damas', selected_piece: 'Ficha seleccionada. Elige una casilla resaltada.',
        mandatory_capture: 'Hay una captura disponible y es obligatorio realizarla.',
        continue_capture: 'Continúa capturando con la misma ficha.',
        checkers_instructions: 'Tus fichas son {color}. Captura todas las fichas rivales o bloquea todos sus movimientos para ganar.',
        checkers_share_code: 'Tus fichas son {color}. Comparte el código para que tu rival juegue con el otro color.',
        checkers_opponent_thinking: 'Tus fichas son {color}. Turno de {opponent}…',
        checkers_ai_game_started: 'Partida de damas iniciada. Tú juegas con las {yourColor}; Gridlock IA juega con las {opponentColor} ({difficulty}).',
        checkers_pieces_x: 'fichas verde lima', checkers_pieces_o: 'fichas azules',
        checkers_color_x: 'verde lima', checkers_color_o: 'azules',
        checkers_rules_title: 'Cómo jugar a las damas',
        checkers_rule_move: 'Las fichas normales avanzan una casilla en diagonal; las damas se mueven una casilla en diagonal en ambas direcciones.',
        checkers_rule_capture: 'Salta sobre una ficha rival adyacente para capturarla. Las capturas son obligatorias.',
        checkers_rule_chain: 'Si puedes capturar otra ficha después de saltar, continúa con la misma ficha.',
        checkers_rule_king: 'Una ficha que llega al borde opuesto se convierte en dama.',
        checkers_rule_win: 'Captura todas las fichas rivales o bloquea todos sus movimientos para ganar.',
        play_checkers_ai: 'Jugar a las damas contra la IA',
        row: 'Fila', column: 'columna', ai_opponent: 'Gridlock IA',
        auth_eyebrow: 'UN RETO ENTRE AMIGOS', welcome_title: 'Haz tu<br>siguiente jugada.',
        intro_copy: 'Empieza una partida, comparte el código y descubre quién consigue tres en línea. Tus estadísticas te acompañan en cada partida.',
        sign_in: 'Iniciar sesión', create_account: 'Crear cuenta', username: 'Nombre de usuario',
        username_hint: 'De 3 a 20 letras, números o guiones bajos', password: 'Contraseña',
        password_hint: 'Al menos 10 caracteres', your_game_room: 'TU SALA DE JUEGO',
        welcome_back: 'Te damos la bienvenida,', sign_out: 'Cerrar sesión', play_now: 'JUGAR',
        game_room: 'Sala de juego', no_game: 'No hay ninguna partida seleccionada', start_game: 'Empezar partida',
        ai_difficulty: 'Dificultad de la IA', easy: 'Fácil — casi aleatoria', medium: 'Media — táctica',
        hard: 'Difícil — imbatible', play_ai: 'Jugar contra la IA', game_code: 'Código de partida',
        enter_code: 'INTRODUCE EL CÓDIGO', join_game: 'Unirse a partida', game_code_caps: 'CÓDIGO DE PARTIDA',
        copy: 'Copiar', opponent: 'OPONENTE', your_games: 'Tus partidas',
        games_empty: 'Tus partidas aparecerán aquí.', score_so_far: 'TU MARCADOR',
        your_stats: 'Tus estadísticas', played: 'Jugadas', wins: 'Victorias', losses: 'Derrotas',
        draws: 'Empates', top_players: 'MEJORES JUGADORES', leaderboard: 'Clasificación',
        footer_tagline: 'Una buena partida está a solo una jugada.', waiting: 'Esperando rival',
        game_over: 'Partida terminada', in_progress: 'En curso', aborted: 'Abandonada', single_player: 'un jugador',
        abort_game: 'Abandonar partida', confirm_abort_game: '¿Seguro que quieres abandonar esta partida? No se puede deshacer.',
        game_aborted: 'Esta partida se ha abandonado.',
        waiting_player: 'Esperando a un jugador', share_code: 'Comparte este código con un amigo. Tú eres X y tu rival es O.',
        your_turn_x: 'Tu turno — eres X.', your_turn: 'Tu turno — eres {symbol}.',
        opponent_thinking: 'Turno de {opponent}…', you_win: '¡Has ganado!',
        player_wins: '¡{player} gana!', draw_result: '¡Empate!',
        games_empty_leaderboard: 'Juega una partida para aparecer en la clasificación.',
        game_created: 'Partida creada. Comparte el código para invitar a alguien.',
        ai_game_started: 'Partida iniciada. Tú eres X y Gridlock IA es O ({difficulty}).',
        joined_game: 'Te has unido a la partida. ¡Buena suerte!', copied: '¡Copiado!',
        copy_failed: 'No se pudo copiar el código. Selecciónalo para copiarlo manualmente.',
        connection_error: 'No se pudo conectar con el servidor. Comprueba la configuración de PHP y la base de datos.',
        invalid_response: 'El servidor devolvió una respuesta no válida (HTTP {status}, {type}). Comprueba los registros PHP y que api.php se ejecute con PHP.',
        error_login: 'Nombre de usuario o contraseña incorrectos.', error_username_taken: 'Ese nombre de usuario ya está en uso.',
        error_auth_required: 'Inicia sesión para continuar.', error_session: 'Tu sesión ha caducado. Recarga la página e inténtalo de nuevo.',
        error_invalid_username_password: 'Introduce un nombre de usuario y una contraseña válidos.',
        error_registration: 'Usa un nombre de 3 a 20 caracteres (letras, números o guiones bajos) y una contraseña de 10 a 72 caracteres.',
        error_game_unavailable: 'Esa partida no está disponible. Comprueba el código o empieza otra.',
        error_own_game: 'No puedes unirte a tu propia partida.', error_turn: 'No es tu turno.',
        error_wait_ai: 'Espera a que la IA haga su jugada.', error_square: 'Esa casilla ya está ocupada.',
        error_not_accepting: 'Esta partida ya no acepta jugadas.', error_game_not_found: 'No se encontró la partida.',
        error_invalid_code: 'Introduce un código válido de 6 caracteres.', error_invalid_move: 'Jugada no válida.',
        error_invalid_game: 'Partida no válida.', error_ai_difficulty: 'Elige una dificultad de IA válida.',
        error_generic: 'Algo salió mal. Inténtalo de nuevo.', wins_short: 'V',
        error_invalid_game_kind: 'Elige un juego válido.',
        error_unknown_action: 'El servidor no admite esta acción. Instala la versión más reciente de api.php y aplica la migración de base de datos necesaria.',
        board_label: 'Tablero de tres en raya', top_left: 'Arriba a la izquierda', top_middle: 'Arriba en el centro',
        top_right: 'Arriba a la derecha', middle_left: 'En medio a la izquierda', center: 'Centro',
        middle_right: 'En medio a la derecha', bottom_left: 'Abajo a la izquierda', bottom_middle: 'Abajo en el centro',
        bottom_right: 'Abajo a la derecha',
    },
};

const apiErrorKeys = new Map([
    ['Incorrect username or password.', 'error_login'],
    ['That username is already taken.', 'error_username_taken'],
    ['Please sign in to continue.', 'error_auth_required'],
    ['Your session has expired. Refresh the page and try again.', 'error_session'],
    ['Enter a valid username and password.', 'error_invalid_username_password'],
    ['Use a 3–20 character username (letters, numbers, underscores) and a password of 10–72 characters.', 'error_registration'],
    ['That game is unavailable. Check the code or start a new game.', 'error_game_unavailable'],
    ['You cannot join your own game.', 'error_own_game'],
    ['It is not your turn.', 'error_turn'],
    ['Wait for the AI to make its move.', 'error_wait_ai'],
    ['That square is already taken.', 'error_square'],
    ['This game is not accepting moves.', 'error_not_accepting'],
    ['Unknown action.', 'error_unknown_action'],
    ['Game not found.', 'error_game_not_found'],
    ['Enter a valid 6-character game code.', 'error_invalid_code'],
    ['Invalid move.', 'error_invalid_move'],
    ['Invalid game.', 'error_invalid_game'],
    ['Choose a valid AI difficulty.', 'error_ai_difficulty'],
    ['Choose a valid game.', 'error_invalid_game_kind'],
    ['A capture is available and must be taken.', 'mandatory_capture'],
    ['Continue capturing with the same piece.', 'continue_capture'],
    ['Something went wrong. Please try again.', 'error_generic'],
]);

let csrf = '';
let authMode = 'login';
let selectedGameId = null;
let selectedGameStatus = null;
let refreshTimer = null;
let language = localStorage.getItem('gridlock-language') || navigator.language.slice(0, 2);
if (!Object.hasOwn(translations, language)) {
    language = 'en';
}

function t(key, values = {}) {
    let text = translations[language][key] || translations.en[key] || key;
    for (const [name, value] of Object.entries(values)) {
        text = text.replaceAll(`{${name}}`, value);
    }
    return text;
}

function translatedError(message) {
    const key = apiErrorKeys.get(message);
    return key ? t(key) : message;
}

function applyTranslations() {
    document.documentElement.lang = language;
    document.title = language === 'en'
        ? 'Gridlock — Multiplayer Tic-Tac-Toe'
        : `Gridlock — ${t('game_room')}`;

    document.querySelectorAll('[data-i18n]').forEach((element) => {
        const key = element.dataset.i18n;
        if (key === 'welcome_title') {
            element.innerHTML = t(key);
        } else {
            element.textContent = t(key);
        }
    });
    document.querySelectorAll('[data-i18n-placeholder]').forEach((element) => {
        element.placeholder = t(element.dataset.i18nPlaceholder);
    });
    document.querySelectorAll('[data-i18n-aria-label]').forEach((element) => {
        element.setAttribute('aria-label', t(element.dataset.i18nAriaLabel));
    });
    document.querySelector('#language-select').value = language;
    document.querySelector('#language-select').setAttribute('aria-label', t('language'));
    document.querySelector('#ai-difficulty').setAttribute('aria-label', t('ai_difficulty'));
    document.querySelector('#game-kind').setAttribute('aria-label', t('choose_game'));
    document.querySelector('.auth-tabs').setAttribute('aria-label', t('account_action'));
    document.querySelector('#copy-code').textContent = t('copy');
    updateGameOptions();
    if (authForm) {
        const action = document.querySelector('.auth-submit');
        action.firstChild.textContent = `${t(authMode === 'login' ? 'sign_in' : 'create_account')} `;
    }
    if (appView.hidden === false) {
        refreshDashboard().catch((error) => setMessage(gameMessage, translatedError(error.message), true));
    }
}

function updateGameOptions() {
    const gameKind = document.querySelector('#game-kind').value;
    const isCheckers = gameKind === 'checkers';
    document.querySelector('#create-ai-game').firstElementChild.textContent = t(isCheckers ? 'play_checkers_ai' : 'play_ai');
    document.querySelector('#game-instructions').hidden = !isCheckers;
}

async function api(action, payload = {}, method = 'POST') {
    const options = { method, headers: { 'Content-Type': 'application/json' } };
    if (method === 'GET') {
        options.headers = {};
    } else {
        options.body = JSON.stringify({ ...payload, csrf, action });
    }

    const response = await fetch(method === 'GET' ? `api.php?action=${encodeURIComponent(action)}` : 'api.php', options);
    const responseText = await response.text();
    let result;
    try {
        result = JSON.parse(responseText);
    } catch {
        const contentType = response.headers.get('content-type') || 'unknown content type';
        throw new Error(t('invalid_response', { status: response.status, type: contentType }));
    }

    if (!response.ok) {
        throw new Error(translatedError(result.error || 'Something went wrong. Please try again.'));
    }
    return result;
}

function setMessage(element, text, isError = false) {
    element.textContent = text;
    element.classList.toggle('is-error', isError);
}

function showAuth() {
    clearInterval(refreshTimer);
    authView.hidden = false;
    appView.hidden = true;
}

function showApp() {
    authView.hidden = true;
    appView.hidden = false;
}

function setAuthMode(mode) {
    authMode = mode;
    const isLogin = mode === 'login';
    document.querySelector('#login-tab').classList.toggle('is-active', isLogin);
    document.querySelector('#register-tab').classList.toggle('is-active', !isLogin);
    document.querySelector('#login-tab').setAttribute('aria-selected', String(isLogin));
    document.querySelector('#register-tab').setAttribute('aria-selected', String(!isLogin));
    document.querySelector('#password').autocomplete = isLogin ? 'current-password' : 'new-password';
    document.querySelector('.auth-submit').firstElementChild.textContent = t(isLogin ? 'sign_in' : 'create_account');
    setMessage(authMessage, '');
}

function renderDashboard(data) {
    document.querySelector('#welcome-name').textContent = data.user.username;
    document.querySelector('#stat-played').textContent = data.user.games_played;
    document.querySelector('#stat-wins').textContent = data.user.wins;
    document.querySelector('#stat-losses').textContent = data.user.losses;
    document.querySelector('#stat-draws').textContent = data.user.draws;

    const gameList = document.querySelector('#game-list');
    gameList.replaceChildren();
    if (data.games.length === 0) {
        const empty = document.createElement('p');
        empty.className = 'empty-state';
        empty.textContent = t('games_empty');
        gameList.append(empty);
    }
    data.games.forEach((game) => {
        const item = document.createElement('div');
        item.className = 'game-list-item';
        item.classList.toggle('is-selected', game.id === selectedGameId);
        const selectButton = document.createElement('button');
        selectButton.type = 'button';
        selectButton.className = 'game-list-select';
        const title = document.createElement('span');
        const opponent = game.mode === 'single_player' ? t('ai_opponent') : game.opponent;
        title.textContent = opponent ? `${t('versus')} ${opponent}` : t('waiting_player');
        const meta = document.createElement('span');
        meta.textContent = `${t(game.game_kind)} · ${game.mode === 'single_player' ? t(game.difficulty) : game.code}`;
        selectButton.append(title, meta);
        selectButton.addEventListener('click', () => selectGame(game.id));
        item.append(selectButton);
        const status = document.createElement('span');
        status.className = 'game-list-status';
        status.textContent = t(game.status === 'waiting' ? 'waiting'
            : game.status === 'active' ? 'in_progress'
                : game.status === 'aborted' ? 'aborted' : 'game_over');
        item.append(status);
        gameList.append(item);
    });

    const leaderboard = document.querySelector('#leaderboard');
    leaderboard.replaceChildren();
    if (data.leaderboard.length === 0) {
        const empty = document.createElement('p');
        empty.className = 'empty-state';
        empty.textContent = t('games_empty_leaderboard');
        leaderboard.append(empty);
    } else {
        data.leaderboard.forEach((player, index) => {
            const row = document.createElement('div');
            row.className = 'leaderboard-row';
            const rank = document.createElement('span');
            rank.className = 'rank';
            rank.textContent = String(index + 1).padStart(2, '0');
            const name = document.createElement('span');
            name.className = 'leader-name';
            name.textContent = player.username;
            const wins = document.createElement('span');
            wins.className = 'leader-wins';
            wins.textContent = `${player.wins} ${t('wins_short')}`;
            row.append(rank, name, wins);
            leaderboard.append(row);
        });
    }
}

async function refreshDashboard() {
    const data = await api('dashboard');
    renderDashboard(data);
}

function renderGame(game) {
    document.querySelector('#game-card').hidden = false;
    currentGame = game;
    const isSinglePlayer = game.mode === 'single_player';
    const isCheckers = game.game_kind === 'checkers';
    const checkersColor = t(game.your_symbol === 'X' ? 'checkers_color_x' : 'checkers_color_o');
    const opponent = isSinglePlayer ? t('ai_opponent') : game.opponent;
    document.querySelector('#game-info-label').textContent = isSinglePlayer ? t('opponent') : t('game_code_caps');
    document.querySelector('#current-code').textContent = isSinglePlayer
        ? `${opponent} · ${t(game.difficulty).split(' — ')[0].toUpperCase()}`
        : game.code;
    document.querySelector('#current-code').classList.toggle('game-opponent', isSinglePlayer);
    document.querySelector('#copy-code').hidden = isSinglePlayer;
    document.querySelector('#board').hidden = isCheckers;
    checkersBoard.hidden = !isCheckers;
    const status = document.querySelector('#game-status');
    status.textContent = game.status === 'waiting' ? t('waiting')
        : game.status === 'finished' ? t('game_over')
            : game.status === 'aborted' ? t('aborted') : t('in_progress');
    status.disabled = game.status !== 'active';
    status.setAttribute('aria-label', game.status === 'active' ? t('abort_game') : status.textContent);
    status.classList.toggle('is-live', game.status === 'active');

    let turnMessage;
    if (game.status === 'waiting') {
        turnMessage = isCheckers
            ? t('checkers_share_code', { color: checkersColor })
            : t('share_code');
    } else if (game.status === 'aborted') {
        turnMessage = t('game_aborted');
    } else if (game.status === 'finished') {
        turnMessage = game.winner
            ? (game.winner_symbol === game.your_symbol
                ? t('you_win')
                : t('player_wins', { player: isSinglePlayer && game.winner_symbol === 'O' ? t('ai_opponent') : game.winner }))
            : t('draw_result');
    } else if (isCheckers) {
        turnMessage = game.turn === game.your_symbol || isSinglePlayer
            ? t('checkers_instructions', { color: checkersColor })
            : t('checkers_opponent_thinking', { color: checkersColor, opponent });
        if (game.forced_piece !== null && game.turn === game.your_symbol) {
            turnMessage = t('continue_capture');
        }
    } else if (isSinglePlayer) {
        turnMessage = t('your_turn_x');
    } else {
        turnMessage = game.turn === game.your_symbol
            ? t('your_turn', { symbol: game.your_symbol })
            : t('opponent_thinking', { opponent: game.opponent || t('opponent') });
    }
    document.querySelector('#turn-message').textContent = turnMessage;

    boardButtons.forEach((button, index) => {
        const value = game.board[index];
        button.textContent = value === '-' ? '' : value;
        button.classList.toggle('mark-x', value === 'X');
        button.classList.toggle('mark-o', value === 'O');
        button.disabled = game.status !== 'active' || game.turn !== game.your_symbol || value !== '-';
    });

    if (isCheckers) {
        renderCheckersBoard(game);
    } else {
        selectedCheckersFrom = null;
    }

    const previousStatus = selectedGameStatus;
    selectedGameStatus = game.status;
    if (previousStatus !== null && previousStatus !== game.status) {
        refreshDashboard().catch((error) => setMessage(gameMessage, translatedError(error.message), true));
    }

    function renderCheckersBoard(game) {
        const legalMoves = game.legal_moves || [];
        if (selectedCheckersFrom !== null && !legalMoves.some((move) => move.from === selectedCheckersFrom)) {
            selectedCheckersFrom = null;
        }

        checkersBoard.replaceChildren();
        checkersButtons = [];
        game.board.forEach((piece, index) => {
            const row = Math.floor(index / 8);
            const column = index % 8;
            const square = document.createElement('button');
            const darkSquare = (row + column) % 2 === 1;
            square.type = 'button';
            square.className = `checkers-square ${darkSquare ? 'is-playable' : 'is-light'}`;
            square.dataset.square = String(index);
            square.setAttribute('aria-label', `${t('row')} ${row + 1}, ${t('column')} ${column + 1}${piece === '-' ? '' : `, ${piece}`}`);
            if (piece !== '-') {
                const belongsToPlayer = piece.toLowerCase() === 'x';
                square.classList.add(belongsToPlayer ? 'piece-x' : 'piece-o');
                square.textContent = piece === piece.toUpperCase() ? '♛' : '●';
            }
            if (selectedCheckersFrom === index) {
                square.classList.add('is-selected');
            }
            if (selectedCheckersFrom !== null && legalMoves.some((move) => move.from === selectedCheckersFrom && move.to === index)) {
                square.classList.add('is-legal-target');
            }
            const playerCanMove = game.status === 'active'
                && game.turn === game.your_symbol
                && legalMoves.length > 0;
            const canStartMove = legalMoves.some((move) => move.from === index);
            const isLegalTarget = selectedCheckersFrom !== null
                && legalMoves.some((move) => move.from === selectedCheckersFrom && move.to === index);
            square.disabled = !darkSquare || !playerCanMove || (!canStartMove && !isLegalTarget);
            square.addEventListener('click', () => onCheckersSquareClick(index));
            checkersButtons.push(square);
            checkersBoard.append(square);
        });
    }

    async function onCheckersSquareClick(square) {
        if (!currentGame) {
            return;
        }
        const legalMoves = currentGame.legal_moves || [];
        const selectedMove = selectedCheckersFrom === null
            ? null
            : legalMoves.find((move) => move.from === selectedCheckersFrom && move.to === square);
        if (selectedMove) {
            selectedCheckersFrom = null;
            await submitMove({ game_id: currentGame.id, from: selectedMove.from, to: selectedMove.to });
            return;
        }

        const canMovePiece = legalMoves.some((move) => move.from === square);
        if (canMovePiece) {
            selectedCheckersFrom = square;
            renderCheckersBoard(currentGame);
            setMessage(gameMessage, t('selected_piece'));
        } else if (selectedCheckersFrom !== null) {
            setMessage(gameMessage, t('mandatory_capture'), true);
        }
    }

}

async function submitMove(move) {
    try {
        const { game } = await api('move', move);
        setMessage(gameMessage, '');
        renderGame(game);
    } catch (error) {
        setMessage(gameMessage, translatedError(error.message), true);
        if (selectedGameId) {
            api('state', { game_id: selectedGameId })
                .then(({ game }) => renderGame(game))
                .catch((refreshError) => setMessage(gameMessage, translatedError(refreshError.message), true));
        }
    }
}

async function selectGame(gameId, gameState = null) {
    selectedGameId = gameId;
    selectedCheckersFrom = null;
    try {
        const game = gameState || (await api('state', { game_id: gameId })).game;
        document.querySelector('#game-kind').value = game.game_kind;
        localStorage.setItem('gridlock-game-kind', game.game_kind);
        updateGameOptions();
        renderGame(game);
        await refreshDashboard();
        clearInterval(refreshTimer);
        refreshTimer = setInterval(() => {
            api('state', { game_id: selectedGameId })
                .then(({ game }) => renderGame(game))
                .catch((error) => setMessage(gameMessage, translatedError(error.message), true));
        }, 2000);
    } catch (error) {
        setMessage(gameMessage, translatedError(error.message), true);
    }
}

async function startApp() {
    showApp();
    await refreshDashboard();
    const list = document.querySelector('#game-list');
    const firstGame = list.querySelector('.game-list-select');
    if (firstGame) {
        firstGame.click();
    }
}

async function abortGame(gameId) {
    if (!window.confirm(t('confirm_abort_game'))) {
        return;
    }
    try {
        const { game } = await api('abort', { game_id: gameId });
        if (selectedGameId === gameId) {
            renderGame(game);
        }
        await refreshDashboard();
    } catch (error) {
        setMessage(gameMessage, translatedError(error.message), true);
    }
}

document.querySelector('#login-tab').addEventListener('click', () => setAuthMode('login'));
document.querySelector('#register-tab').addEventListener('click', () => setAuthMode('register'));
document.querySelector('#game-status').addEventListener('click', () => {
    if (selectedGameId && currentGame?.status === 'active') {
        abortGame(selectedGameId);
    }
});

document.querySelector('#language-select').addEventListener('change', (event) => {
    language = event.target.value;
    localStorage.setItem('gridlock-language', language);
    setMessage(gameMessage, '');
    applyTranslations();
    if (selectedGameId) {
        api('state', { game_id: selectedGameId })
            .then(({ game }) => renderGame(game))
            .catch((error) => setMessage(gameMessage, translatedError(error.message), true));
    }
});

document.querySelector('#ai-difficulty').value = localStorage.getItem('gridlock-ai-difficulty') || 'medium';
document.querySelector('#ai-difficulty').addEventListener('change', (event) => {
    localStorage.setItem('gridlock-ai-difficulty', event.target.value);
});

document.querySelector('#game-kind').value = localStorage.getItem('gridlock-game-kind') || 'tic_tac_toe';
updateGameOptions();
document.querySelector('#game-kind').addEventListener('change', (event) => {
    localStorage.setItem('gridlock-game-kind', event.target.value);
    updateGameOptions();
});

authForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const formData = new FormData(authForm);
    try {
        await api(authMode === 'login' ? 'login' : 'register', {
            username: formData.get('username'),
            password: formData.get('password'),
        });
        authForm.reset();
        await startApp();
    } catch (error) {
        setMessage(authMessage, translatedError(error.message), true);
    }
});

document.querySelector('#logout-button').addEventListener('click', async () => {
    try {
        await api('logout');
        selectedGameId = null;
        selectedGameStatus = null;
        document.querySelector('#game-card').hidden = true;
        showAuth();
    } catch (error) {
        setMessage(gameMessage, translatedError(error.message), true);
    }
});

document.querySelector('#create-game').addEventListener('click', async () => {
    try {
        const gameKind = document.querySelector('#game-kind').value;
        localStorage.setItem('gridlock-game-kind', gameKind);
        const { game } = await api('create', { game_kind: gameKind });
        selectedGameStatus = null;
        setMessage(gameMessage, t('game_created'));
        await refreshDashboard();
        await selectGame(game.id);
    } catch (error) {
        setMessage(gameMessage, translatedError(error.message), true);
    }
});

document.querySelector('#create-ai-game').addEventListener('click', async () => {
    try {
        const difficulty = document.querySelector('#ai-difficulty').value;
        const gameKind = document.querySelector('#game-kind').value;
        localStorage.setItem('gridlock-ai-difficulty', difficulty);
        localStorage.setItem('gridlock-game-kind', gameKind);
        const { game } = await api('create_ai', { difficulty, game_kind: gameKind });
        selectedGameStatus = null;
        const difficultyName = t(difficulty).split(' — ')[0].toLowerCase();
        setMessage(gameMessage, gameKind === 'checkers'
            ? t('checkers_ai_game_started', {
                difficulty: difficultyName,
                yourColor: t(game.your_symbol === 'X' ? 'checkers_pieces_x' : 'checkers_pieces_o'),
                opponentColor: t(game.your_symbol === 'X' ? 'checkers_pieces_o' : 'checkers_pieces_x'),
            })
            : t('ai_game_started', { difficulty: difficultyName }));
        await refreshDashboard();
        await selectGame(game.id);
    } catch (error) {
        setMessage(gameMessage, translatedError(error.message), true);
    }
});

document.querySelector('#join-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    const code = document.querySelector('#game-code').value.trim().toUpperCase();
    try {
        const { game } = await api('join', { code });
        document.querySelector('#game-code').value = '';
        document.querySelector('#game-kind').value = game.game_kind;
        localStorage.setItem('gridlock-game-kind', game.game_kind);
        selectedGameStatus = null;
        setMessage(gameMessage, t('joined_game'));
        await selectGame(game.id, game);
    } catch (error) {
        setMessage(gameMessage, translatedError(error.message), true);
    }
});

boardButtons.forEach((button) => {
    button.addEventListener('click', async () => {
        await submitMove({
            game_id: selectedGameId,
            cell: Number(button.dataset.cell),
        });
    });
});

document.querySelector('#copy-code').addEventListener('click', async () => {
    try {
        await navigator.clipboard.writeText(document.querySelector('#current-code').textContent);
        document.querySelector('#copy-code').textContent = t('copied');
        setTimeout(() => { document.querySelector('#copy-code').textContent = t('copy'); }, 1500);
    } catch {
        setMessage(gameMessage, t('copy_failed'), true);
    }
});

(async function initialize() {
    applyTranslations();
    try {
        const session = await api('me', {}, 'GET');
        csrf = session.csrf;
        if (session.authenticated) {
            await startApp();
        } else {
            showAuth();
        }
    } catch {
        showAuth();
        setMessage(authMessage, t('connection_error'), true);
    }
}());
