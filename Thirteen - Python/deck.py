from card import Card
from sort import Sort
import random
import copy

"""
2012 Peter Phan
Class: Deck
--------------------
The Deck class is simply an array of cards.  It mostly functions as a simple
deck.  It has the ability to shuffle its deck, deal a card, and put a card back
in its deck.

Each instance of card class has
	value: representing 0-12
	suit: one of ["H", "D", "C", "S"]
	cardValue: actual character of card (e.g. "J", "2", "8", "A")
"""
NUM_OF_CARDS = 52

class Deck(object):
	cards = []
	CARDS = ["3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K", "A", "2"]
	SUITS = ["S", "C", "D", "H"]

	# initializes a shuffled deck
	def __init__(self):
		for i in range(NUM_OF_CARDS):
			self.cards.append(Card(i))
		self.shuffle()

	def sort(self, cmpfn):
		Sort.qsort(self.cards, cmpfn)
	
	def size(self):
		return len(self.cards)

	def shuffle(self):
		for i in range(len(self.cards)-1):
			exchange = i + random.randint(0,len(self.cards)-1-i)
			self.cards[i], self.cards[exchange] = self.cards[exchange], self.cards[i]
	
	def deal(self):
		return self.cards.pop()

	def put(self, card):
		self.cards.append(card)

	def peek(self):
		return self.cards[len(self.cards)-1]