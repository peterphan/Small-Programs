"""
2012 Peter Phan
---------------
Game: Thirteen aka Tien Len
Rules and gameplay: http://en.wikipedia.org/wiki/Tien_len

Text-based game
Multiplayer - not viable unless there's a way for multiple people to connect to a game

Todo: Add computer opponent
"""

# IMPORTS
from deck import Deck
from sort import Sort
import time

"""
Class Player
------------
Provides all the methods necessary for a player in Tien Len
Keeps track of the player's hand and provides methods necessary for playing the game
"""
class Player:
	starting_num_of_cards = 13

	def __init__(self, name):
		self.hand = []
		self.handDict = {}
		self.name = name
		self.playing = True
		self.passed = False

	# adds a card to the hand
	def addCardToHand(self, card):
		self.hand.append(card)
		self.handDict[card.cardValue + card.suit] = card

	# takes a card from the hand and returns it (plays it)
	def playCard(self, key):
		card = self.handDict.pop(key, None)
		self.hand.remove(card)
		if len(self.hand) == 0:
			self.playing = False
		return card

	# necessary for ease of reading cards
	# can spot straights and of a kinds more easily
	def sortHand(self, cmpfn):
		Sort.qsort(self.hand, compareCard)

	def cardsLeft(self):
		return len(self.hand)

PAUSE_TIME = 1

# Hearts > Diamonds > Clubs > Spades
# and given numerical value to represent them as such
SUIT_VALUE = {
		"H" : 4,
		"D" : 3,
		"C" : 2,
		"S" : 1
	}

# Lack of a graphical representation necesitates action options for gameplay
ACTIONS = {
		"complete" : "complete turn or pass turn onto the next player",
		"sort"     : "sort your hand", 
		"choose"   : "choose which cards to play",
		"remove"   : "remove cards from list of cards you've chosen to play",
		"redisplay": "redisplay your hand",
		"options"  : "displays list of options available"
		}

CHOOSE_OPTIONS = {
		"done"						: "complete choosing cards to play",
		"sort"						: "sort your hand", 
		"remove"					: "remove cards from list of cards you've chosen to play",
		"options"					: "displays list of options available",
		"<card value + card suit>"	: "put this card up for play and displays current hand, hand to beat, and hand up for consideration"
		}

REMOVE_OPTIONS = {
		"done"						: "complete removing cards",
		"sort"						: "sort your hand", 
		"choose"					: "choose cards from list of cards to play",
		"options"					: "displays list of options available",
		"<card value + card suit>"	: "put this card up for play and displays current hand, hand to beat, and hand up for consideration"
		}

"""
Prints the introduction to the game
todo: tutorial print
"""
def introduction():
	print "Welcome to the game of Thirteen (Tien Len)"
	tutorial = raw_input("Do you want a tutorial of how to play (enter 'yes' or 'no' without quotes)? ")
	while tutorial != 'yes' and tutorial != 'no':
		print "Sorry, I didn't get that."
		tutorial = raw_input("Please enter 'yes' or 'no': ")

	if tutorial == 'yes':
		# do something
		return 0

def printAllRelevantHands(player, cardsToPlay, cardsToBeat):
	print "Current cards to play::", handToStr(cardsToPlay)
	if len(cardsToBeat) > 0:
		print "Cards to beat::", handToStr(cardsToBeat)
	print "Your current hand::", handToStr(player.hand)
	print

def containsATwo(hand):
	for card in hand:
		if card.cardValue == "2":
			return True
	return False

# checks if a hand a player wants to put down is valid
# need to account how many straight
# and the occurence of each hand
def isAValidHand(hand):
	if len(hand) == 0:
		return True
	handType = typeOfPlay(hand)

	# check for breaks in straight
	currValue = hand[0].value
	for card in hand:
		if card.value == currValue:
			currValue += 1
		elif card.value > currValue:
			return False


	if handType["straight"] > 1 and containsATwo(hand):
		# can't include a 2 in straight
		print "Error: Invalid hand. Can't include a 2 in a straight"
		print
		return False

	if handType["straight"] == 2:
		# need a straight of at least 3
		print "Error: Straight needs at least 3"
		print
		return False

	if handType["ofAKind"] > 1:
		# check if each card in the hand has the same "of a kind" count for each card
		currentVal = hand[0].value
		currentCount = 0
		for card in hand:
			if currentVal == card.value:
				currentCount += 1
			elif currentCount != handType["ofAKind"]:
				print "Error: Need the same number of a kind for each card"
				print
				return False
			else:
				currentCount = 1
				currentVal = card.value
	return True

# checks how many straight the hand is
def numOfStraight(cardsPlayed):
	straight = 0
	toLookFor = -1
	for card in cardsPlayed:
		if toLookFor == -1:
			toLookFor = card.value
		if card.value == toLookFor:
			straight += 1
		toLookFor += 1

		# don't include 2 in straight possibility
		if card.cardValue == "2":
			break
	return straight

# checks how many of a kind there are
def numOfAKind(cardsPlayed):
	count = 0
	toLookFor = -1
	for card in cardsPlayed:
		if toLookFor == -1:
			toLookFor = card.value
		if card.value == toLookFor:
			count += 1

	return count

"""
cardsPlayed should be a list of cards
stores what type of play it is in a dict
"""
def typeOfPlay(cardsPlayed):
	Sort.qsort(cardsPlayed, compareCard)
	return {"straight" : numOfStraight(cardsPlayed), "ofAKind" : numOfAKind(cardsPlayed)}

# puts array of cards into a string
def handToStr(cards):
	hand = ""
	for card in cards: 
		hand += card.cardValue + card.suit + " "
	return hand

# sorts hand in ascending value order
def compareCard(card1, card2):
	if card1.value < card2.value:
		return -1
	if card1.value > card2. value:
		return 1

	if SUIT_VALUE[card1.suit] < SUIT_VALUE[card2.suit]:
		return -1
	elif SUIT_VALUE[card1.suit] > SUIT_VALUE[card2.suit]:
		return 1
	# return 0 should never reach unless this function is used to sort
	return 0

def getNumPlayers():
	numOfPlayers = raw_input("Enter how many players will be playing: ")
	while not numOfPlayers.isdigit() or int(numOfPlayers) < 2 or int(numOfPlayers) > 4:
			numOfPlayers = raw_input("Enter a number between 2 and 4: ")
	return int(numOfPlayers)

def getAction():
	action = raw_input("Enter what you want to do (type 'options' for list of actions available): ")
	while not action in ACTIONS.keys():
		action = raw_input("Enter a valid action! ")
	return action

def removeCards(player, cardsToPlay, cardsToBeat):
	print "Choose a card to remove by entering the card"
	printAllRelevantHands(player, cardsToPlay, cardsToBeat)

	# create dict for removing cards
	toPlayDict = {}
	for card in cardsToPlay:
		toPlayDict[card.cardValue+card.suit] = card

	action = raw_input("What card or option do you want to take (enter 'options' for a list of options)? ")
	while action != "done":
		while not action in REMOVE_OPTIONS.keys() and not action in toPlayDict.keys():
			action = raw_input("What card or option do you want to take (enter 'options' for a list of options)? ")

		if action == "done": return
		if action == "sort":
			player.sortHand(compareCard)
			Sort.qsort(cardsToBeat, compareCard)
			Sort.qsort(cardsToPlay, compareCard)
			print "Cards sorted"
		elif action == "choose":
			chooseCards(player, cardsToPlay, cardsToBeat)
			return
		elif action == "options":
			print
			for option, description in REMOVE_OPTIONS.iteritems():
				print option + " ::: " + description
			print
		else:
			# remove card from to play and add it to hand
			print "Removing", action, "from cards to play"
			card = toPlayDict.pop(action, None)
			player.addCardToHand(card)
			cardsToPlay.remove(card)

		printAllRelevantHands(player, cardsToPlay, cardsToBeat)
		action = None

def chooseCards(player, cardsToPlay, cardsToBeat):
	print "Choose a card to play by entering the card"
	printAllRelevantHands(player, cardsToPlay, cardsToBeat)
	
	action = raw_input("What card or option do you want to take (enter 'options' for a list of options)? ")
	while action != "done":
		while not action in CHOOSE_OPTIONS.keys() and not action in player.handDict.keys():
			action = raw_input("What card or option do you want to take (enter 'options' for a list of options)? ")

		if action == "done": return
		if action == "sort":
			player.sortHand(compareCard)
			Sort.qsort(cardsToBeat, compareCard)
			Sort.qsort(cardsToPlay, compareCard)
			print "Cards sorted"
		elif action == "remove":
			removeCards(player, cardsToPlay, cardsToBeat)
			return
		elif action == "options":
			print
			for option, description in CHOOSE_OPTIONS.iteritems():
				print option + " ::: " + description
			print
		else:
			print "Putting", action, "up for play"
			cardsToPlay.append(player.playCard(action))
			
		printAllRelevantHands(player, cardsToPlay, cardsToBeat)
		action = None

def doAction(player, action, cardsToPlay, cardsToBeat):
	if action == "sort":
		player.sortHand(compareCard)
		print "Here is your newly sorted hand"
		printAllRelevantHands(player, cardsToPlay, cardsToBeat)

	if action == "choose":
		chooseCards(player, cardsToPlay, cardsToBeat)

	if action == "remove":
		removeCards(player, cardsToPlay, cardsToBeat)

	if action == "options":
		print
		for option, description in ACTIONS.iteritems():
			print option + " ::: " + description

	if action == "redisplay":
		printAllRelevantHands(player, cardsToPlay, cardsToBeat)
	print

# assumes hand is valid
# just needs to compare straight number and of a kind
def handCanBePlayed(cardsToPlay, typeInPlay, cardsToBeat):
	typeToPlay = typeOfPlay(cardsToPlay)
	if typeToPlay["straight"] != typeInPlay["straight"]:
		return False
	if typeToPlay["ofAKind"] != typeInPlay["ofAKind"]:
		return False

	# number of straight and of a kind are the same
	# now compare values
	highestToPlayCard = cardsToPlay[len(cardsToPlay)-1]
	highestInPlayCard = cardsToBeat[len(cardsToBeat)-1]
	if highestInPlayCard.value > highestToPlayCard.value:
		printPause("Need a higher card value to beat the cards in play\n")
		print
		time.sleep(PAUSE_TIME)
		return False
	if highestInPlayCard.value == highestToPlayCard.value:
		if SUIT_VALUE[highestInPlayCard.suit] > SUIT_VALUE[highestToPlayCard.suit]:
			printPause("Need a higher suit to win\n")
			return False

	return True

def printPause(msg):
	print msg
	time.sleep(PAUSE_TIME)

def performActions(player, cardsToBeat, cardsToPlay):
	if len(cardsToBeat) == 0:
		print "You are the current leader of this round!"
	else:
		print "You need to beat whatever cards is currently on the table"
	print "To start putting cards down, type 'choose'"
	print "Or if you can't beat the current cards, just pass your turn"
	print "by putting entering 'complete'"

	action = getAction()

	# let user decide all the things to do this turn
	while action != "complete":
		doAction(player, action, cardsToPlay, cardsToBeat)
		action = getAction()

	validHand = isAValidHand(cardsToPlay)

	# no cards currently on the table
	if len(cardsToBeat) == 0:
		# note that the player is participating in the round
		player.passed = False
		if len(cardsToPlay) == 0 or not validHand:
			printPause("You are the current leader!")
			printPause("You must play something valid!\n")
			return performActions(player, cardsToBeat, cardsToPlay)
		else:
			# set current hand on table
			printAllRelevantHands(player, cardsToPlay, cardsToBeat)
			return list(cardsToPlay)

	else:
		# have to pass or beat the leader
		typeInPlay = typeOfPlay(cardsToBeat)
		if len(cardsToPlay) == 0:
			printPause("You are choosing to pass\n")
			player.passed = True
			return cardsToBeat

		elif not validHand or not handCanBePlayed(cardsToPlay, typeInPlay, cardsToBeat):
			print "The hand you want to play is not valid!"
			print "You need to have a straight of", typeInPlay["straight"]
			print "and a 'of a kind' of", typeInPlay["ofAKind"]
			print "to match the leader"
			printPause("")
			print "Choose or remove some cards to match the current leader."
			print "The leader's hand is:", handToStr(cardsToBeat)
			print "Your current hand is:", handToStr(cardsToPlay)
			print "You have these cards to choose from:", handToStr(player.hand)
			printPause("")
			return performActions(player, cardsToBeat, cardsToPlay)

		else:
			# set current hand on table
			printAllRelevantHands(player, cardsToPlay, cardsToBeat)
			player.passed = False
			return list(cardsToPlay)

def main():

	introduction()

	numOfPlayers = getNumPlayers()
	players = []

	for player in range(numOfPlayers):
		name = raw_input("Enter Player " + str(player + 1) + "'s name: ")
		players.append(Player(name))

	print "Shuffling and dealing cards..."
	# create deck
	deck = Deck()

	# while not everyone has been dealt 13 cards
	while players[numOfPlayers-1].cardsLeft() != players[numOfPlayers-1].starting_num_of_cards:
		for player in players:
			player.addCardToHand(deck.deal())
	print "Finished dealing!"

	# last player to put down cards
	lastPlayerPlayed = 0
	
	# cards currently on the table to beat
	cardsToBeat = []

	# cards current player is currently considering to play
	cardsToPlay = []

	while numOfPlayers > 1:

		# loop through player number instead of using
		# "for player in plays:
		# makes code easier to write
		for playerNum in range(numOfPlayers):
			player = players[playerNum]

			# player skipped his turn so leave him out of the round
			if player.passed:
				continue

			# check if the last hand played was by this player
			if lastPlayerPlayed == playerNum:
				# reset cards and reset passed
				cardsToBeat = []
				cardsToPlay = []
				for p in players:
					p.passed = False

			print "It is," player.name + "'s turn!"
			printAllRelevantHands(player, cardsToPlay, cardsToBeat)
			cardsToBeat = performActions(player, cardsToBeat, cardsToPlay)
			cardsToPlay = []

			if not player.passed:
				lastPlayerPlayed = playerNum

			# signal that the player is done playing
			if len(player.hand) == 0:
				player.playing = False

			if not player.playing:
				players.remove(player)
				numOfPlayers -= 1
				print player.name + " is done!"
				print "Moving on to the next player\n"
				break

			printPause("Moving on to the next player\n")

main()