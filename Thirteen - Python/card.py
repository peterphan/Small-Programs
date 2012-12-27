"""
2012 Peter Phan
Class: Card
------------------------------------------------------
The Card class is internally represented as
an integer from 0-51.

Values from 0-12 represent Spades
Values from 13-25 represent Clubs
Values from 26-38 represent Diamond
Values from 39-51 represent Hearts

For ease of comparison between the value of the cards,
the numerical value corresponds to these card values:

0 - 3
1 - 4
2 - 5
3 - 6
4 - 7
5 - 8
6 - 9
7 - 10
8 - J
9 - Q
10 - K
11 - A
12 - 2

Note: Does not represent extraneous card values (Jokers, etc)
"""

NUM_CARDS_PER_PLAYER = 13
CARD_VALUE_DICT = {
			8: 'J',
			9: 'Q',
			10: 'K',
			11: 'A',
			12: '2'
		}
SUIT_LIST = ["H", "D", "C", "S"]

class Card(object):
	def __init__(self, value, **valueDict):
		# identity is a number in 0-51
		self.identity = value

		# H, D, C, or S
		self.suit = self.__getSuit()

		# value from 0-12
		self.value = value % NUM_CARDS_PER_PLAYER

		# character value on card
		self.cardValue = self.__getCardValue()

	def __getSuit(self):
		if self.identity <= 12:
			# Spade
			return "S"
		elif self.identity <= 25:
			# Club
			return "C"
		elif self.identity <= 38:
			# Diamond
			return "D"
		else:
			# Heart
			return "H"

	def __getCardValue(self):
		if self.value <= 7:
			# 7 repesents 10 and anything below this
			# value represents a numerical value
			return str(self.value+3)
		return CARD_VALUE_DICT[self.value]

