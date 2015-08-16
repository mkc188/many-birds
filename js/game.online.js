var game_state = {
	// browser size and scale
	scWidth: 0,
	scHeight: 0,
	scScale: 1,

	// background image width, for calc repeating background
	bgWidth: 256,
	bgHeight: 512,

	// default speed for elements
	gravity: 1000,


	// default game map (the pipes)
	map: 'agY96PZUtrLc3gpzMk4taFRWwNHP5XPKZlr3X6UQEazae5puq8XZcGZHjShox0fb5jKSqLO77lvpvRYkcj1gXUGpAQfiqNXlsLibejxr1v0IihjKrlMuRHbcqABIWht6G6eWkwQaQEDv3f6h5m6mLvya8LdoNbs5UvO1Wo8XisMjs0vM1Yif6nq6Y63BW1ZKn8pYkq3ggPrskI5gt819lacJ86tqNmhg6pUDVS2dXoHEF98gWiW6V8nXzVfz2F2',

	// game status
	isDead: false,
	myScore: 0,

	// ref to game world
	bird: null,
	ground: null,
	pipes: null,
	sock: null,
	scoreText: null,
	sprite: null,
	score: 0,
	ggText: null,
	retryText: null,
	highScore: 0,

	topPipeY: -105,
	bottomPipeY: 505,
	fgSpeed: -200,
	flapSpeed: -250,
	flapAngle: 1,
	bgSpeed: -50,

	myID: null,
	opponents: {},

	ip: 'localhost',
	port: '9000',

	// funcions
	restart: function() {
		this.isDead = false;
		this.opponents = {};
		myID: null;
		bird: null;
		ground: null;
		pipes: null;
		sock: null;
		game.state.start('main');
	},
};

/**
 *  Game State: boot
 */
game_state.boot = function() {};
game_state.boot.prototype = {
	preload: function() {
		this.load.image('preloaderBar', 'img/game/get_ready.png');
	},

	create: function() {
		this.input.maxPointers = 1;
		this.state.start('preloader');
	},
};

/**
 *  Game State: preloader
 */
game_state.preloader = function() {};
game_state.preloader.prototype = {
	preload: function() {
		game_state.scHeight = this.game.height;
		game_state.scWidth = this.game.width;
		game_state.scScale = game_state.scHeight / game_state.bgHeight;

		this.game.stage.backgroundColor = "#D7DEFC";

		// preload bar
		this.preloadBar = this.add.sprite(game_state.scWidth / 2, game_state.scHeight / 2, 'preloaderBar');
		this.preloadBar.anchor.setTo(0.5, 0.5);
		this.preloadBar.scale.x = game_state.scScale;
		this.preloadBar.scale.y = game_state.scScale;
		this.load.setPreloadSprite(this.preloadBar);

		// preload resources into game
		this.load.image('backGround', game_state.bg_img);
		this.load.image('ground', 'img/game/ground.png');
		this.game.load.spritesheet('bird', game_state.bird_img, game_state.bird_width, game_state.bird_height, 3);
		this.load.spritesheet('pipe', 'img/game/pipes.png', 78, 480, 2);
	},

	create: function() {
		this.preloadBar.cropEnabled = false;
		// all loaded, turn to main state
		this.state.start('main');
	},
};

/**
 *  Game State: main
 */
game_state.main = function() {};
game_state.main.prototype = {
	preload: function() {
		game_state.scHeight = this.game.height;
		game_state.scWidth = this.game.width;
		game_state.scScale = game_state.scHeight / game_state.bgHeight;

		online.start();
	},

	create: function() {
		game_state.gameWorld = this;

		game_state.score = 0;

		// enable game physics system
		this.game.physics.startSystem(Phaser.Physics.ARCADE);
		this.game.physics.arcade.gravity.y = game_state.gravity;

		// repeating background
		game_state.bg = new Background(this.game, 0, 0, game_state.scWidth / game_state.scScale, game_state.scHeight / game_state.scScale, 'backGround');
		this.game.add.existing(game_state.bg);

		// repeating ground
		game_state.ground = new Ground(this.game, 0, (game_state.bgHeight - 110) * game_state.scScale, game_state.scWidth / game_state.scScale, 128);
		this.game.add.existing(game_state.ground);

		game_state.pipes = this.game.add.group();

		// current user's character
		game_state.bird = new Bird(this.game, (game_state.scWidth * 0.3) * game_state.scScale, (game_state.bgHeight * 0.2) * game_state.scScale);
		this.game.add.existing(game_state.bird);

		// animate the character
		game_state.bird.animations.add('flap');
		game_state.bird.animations.play('flap', 12, true);

		game_state.scoreText = this.game.add.text(0, 0, "", {
			font: '32px "Press Start 2P"',
			fill: '#fff',
			stroke: '#430',
			strokeThickness: 6,
			align: 'center'
		});
		game_state.scoreText.anchor.setTo(0.5, 0.5);
		game_state.sprite = this.game.add.sprite(0, 0);
		game_state.sprite.fixedToCamera = true;
		game_state.sprite.addChild(game_state.scoreText);
		game_state.sprite.cameraOffset.x = this.game.width / 2;
		game_state.sprite.cameraOffset.y = this.game.height / 5;
		game_state.scoreText.setText(game_state.score);


		game_state.ggText = this.game.add.text(this.game.width / 2, this.game.width / 3, "", {
			font: '64px "Press Start 2P"',
			fill: '#fff',
			stroke: '#430',
			strokeThickness: 6,
			align: 'center'
		});
		game_state.ggText.anchor.setTo(0.5, 0.5);
		game_state.ggText.setText('GG. You are Weak.');
		game_state.ggText.visible = false;

		game_state.retryText = this.game.add.text(this.game.width / 2, this.game.width / 2, "", {
			font: '48px "Press Start 2P"',
			fill: '#fff',
			stroke: '#430',
			strokeThickness: 6,
			align: 'center'
		});
		game_state.retryText.anchor.setTo(0.5, 0.5);
		game_state.retryText.setText('Click to retry..');
		game_state.retryText.visible = false;

		this.input.onDown.add(game_state.bird.flap, game_state.bird);

		// add a timer
		this.pipeGenerator = this.game.time.events.loop(Phaser.Timer.SECOND * 2, this.generatePipes, this);
		this.pipeGenerator.timer.start();
	},

	update: function() {
		// enable collisions between the bird and the ground
		this.game.physics.arcade.collide(game_state.bird, game_state.ground, this.deathHandler, null, this);

		// enable collisions between the bird and each group in the pipes group
		game_state.pipes.forEach(function(pipeGroup) {
			this.game.physics.arcade.collide(game_state.bird, pipeGroup, this.deathHandler, null, this);
		}, this);
	},

	shutdown: function() {
		game_state.bird.destroy();
		game_state.pipes.destroy();
	},

	deathHandler: function() {
		// stop the bird falling
		game_state.bird.body.gravity.y = 0;
		game_state.bird.body.velocity.y = 0;
		game_state.bird.body.velocity.x = 0;
		game_state.bg.autoScroll(0, 0);
		game_state.ground.autoScroll(0, 0);
		game_state.pipes.forEach(function(pipeGroup) {
			pipeGroup.setAll('body.velocity.x', 0);
		}, this);
		this.pipeGenerator.timer.stop();
		game_state.ggText.visible = true;
		game_state.retryText.visible = true;
		if (game_state.isDead == false) {
			if (game_state.highScore < game_state.score) {
				game_state.highScore = game_state.score;
			}
			online.end(game_state.score);
		}

		// game end process
		game_state.isDead = true;
	},
	generatePipes: function() {
		var pipeY = this.game.rnd.integerInRange(-100, 100);
		var pipeGroup = game_state.pipes.getFirstExists(false);
		if (!pipeGroup) {
			pipeGroup = new PipeGroup(this.game, game_state.pipes);
		}
		pipeGroup.reset(this.game.width + 39, pipeY);
	}
};



var Background = function(game, x, y, width, height, key) {
	Phaser.TileSprite.call(this, game, x, y, width, height, key);
	this.autoScroll(game_state.bgSpeed, 0);

	this.game.physics.arcade.enableBody(this);

	this.body.allowGravity = false;
	this.body.immovable = true;
};
Background.prototype = Object.create(Phaser.TileSprite.prototype);
Background.prototype.constructor = Background;
Background.prototype.update = function() {};



var Ground = function(game, x, y, width, height) {
	Phaser.TileSprite.call(this, game, x, y, width, height, 'ground');
	this.autoScroll(game_state.fgSpeed, 0);

	this.game.physics.arcade.enableBody(this);

	this.body.allowGravity = false;
	this.body.immovable = true;
};
Ground.prototype = Object.create(Phaser.TileSprite.prototype);
Ground.prototype.constructor = Ground;
Ground.prototype.update = function() {};



var Bird = function(game, x, y) {
	Phaser.Sprite.call(this, game, x, y, 'bird');
	this.anchor.setTo(0.5, 0.5);
	this.animations.add('flap');
	this.animations.play('flap', 12, true);

	this.game.physics.arcade.enableBody(this);
	this.body.collideWorldBounds = true;
};
Bird.prototype = Object.create(Phaser.Sprite.prototype);
Bird.prototype.constructor = Bird;
Bird.prototype.update = function() {
	if (this.angle < 90) {
		this.angle += game_state.flapAngle;
	}
};
Bird.prototype.flap = function() {
	if (game_state.isDead) {
		game_state.restart();
	} else {
		this.body.velocity.y = game_state.flapSpeed;
		this.game.add.tween(this).to({
			angle: -40
		}, 100).start();
	}
};



var Pipe = function(game, x, y, frame) {
	Phaser.Sprite.call(this, game, x, y, 'pipe', frame);
	this.anchor.setTo(1, 0.5);
	this.game.physics.arcade.enableBody(this);

	this.body.allowGravity = false;
	this.body.immovable = true;
};
Pipe.prototype = Object.create(Phaser.Sprite.prototype);
Pipe.prototype.constructor = Pipe;
Pipe.prototype.update = function() {};



var PipeGroup = function(game, parent) {
	Phaser.Group.call(this, game, parent);

	this.topPipe = new Pipe(this.game, 0, game_state.topPipeY, 0);
	this.bottomPipe = new Pipe(this.game, 0, game_state.bottomPipeY, 1);
	this.add(this.topPipe);
	this.add(this.bottomPipe);

	this.setAll('body.velocity.x', game_state.fgSpeed);
};
PipeGroup.prototype = Object.create(Phaser.Group.prototype);
PipeGroup.prototype.constructor = PipeGroup;
PipeGroup.prototype.update = function() {
	this.checkWorldBounds();
};
PipeGroup.prototype.checkWorldBounds = function() {
	if (!this.topPipe.inWorld) {
		this.exists = false;
	}
};
PipeGroup.prototype.reset = function(x, y) {
	this.topPipe.reset(0, game_state.topPipeY);
	this.bottomPipe.reset(0, game_state.bottomPipeY);
	this.x = x;
	this.y = y;
	this.setAll('body.velocity.x', game_state.fgSpeed);
	this.exists = true;
	game_state.score += 1;
	game_state.scoreText.setText(game_state.score);
};
