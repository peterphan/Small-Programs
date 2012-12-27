"""
2012 Peter Phan
Python class that exports a way to sort a list.
The sorting options provided are quicksort and mergesort.

A comparison function can be passed in the functions to determine
the sorting procedure.
"""
import random

# Default Comparison Function
# This is used if a comparison function isn't suppled
def defaultCompFn(left, right):
	if left < right:
		return -1
	if left == right:
		return 0
	return 1

class Sort:

	""" Quick Sort
		Usage:
			sequence = [3,1,2]
			Sort.qsort(sequence)
			#Sort.qsort(sequence, cmpfn)
			print sequence
			# [1, 2, 3]
	"""
	@staticmethod
	def qsort(sequence, *callback):
		if not callback:
			#set default callback if cmpfn wasn't provided
			callback = defaultCompFn
		else:
			callback = callback[0]

		Sort.__quicksort(sequence, 0, len(sequence)-1, callback)

	@staticmethod
	def __quicksort(sequence, left, right, callback):
		if right <= left or len(sequence) <= 1:
			return
		pivot = sequence[random.randint(left, right)]

		l = left
		r = right

		# keep looping until left and right indices have crossed
		while l <= r:
			while callback(sequence[l], pivot) == -1:
				l += 1
			while callback(sequence[r], pivot) == 1:
				r -= 1

			if l <= r:
				# simultaneous swap
				sequence[l], sequence[r] = sequence[r], sequence[l]
				l += 1
				r -= 1

		Sort.__quicksort(sequence, left, r, callback)
		Sort.__quicksort(sequence, l, right, callback)


	""" Merge Sort
		Usage:
			sequence = [3,1,2]
			Sort.mergesort(sequence)
			#Sort.mergesort(sequence, cmpfn)
			print sequence
			# [1, 2, 3]
	"""
	@staticmethod
	def mergesort(sequence, *callback):
		a = random.randint(5, 10)
		if not callback:
			#set default callback if cmpfn wasn't provided
			callback = defaultCompFn
		else:
			callback = callback[0]

		sequenceLength = len(sequence)
		if sequenceLength == 1:
			return sequence

		#split the sequence into two lists
		listA = [sequence[i] for i in range(0, sequenceLength/2)]
		listB = [sequence[i] for i in range(sequenceLength/2, sequenceLength)]

		Sort.mergesort(listA)
		Sort.mergesort(listB)

		Sort.__mergeList(sequence, listA, listB)

	@staticmethod
	def __mergeList(sequence, listA, listB):
		aLength = len(listA)
		bLength = len(listB)
		aIndex = 0
		bIndex = 0
		sequenceIndex = 0

		# merges listA and listB into a sorted sequence
		while True:
			# reached the end of list A, dump all of B into combined list
			if aIndex == aLength:
				for i in range(bIndex, bLength):
					sequence[sequenceIndex] = listB[i]
					sequenceIndex += 1
				break;
					
			# reached the end of list B, dump all of A into combined list
			if bIndex == bLength:
				for i in range(aIndex, aLength):
					sequence[sequenceIndex] = listA[i]
					sequenceIndex += 1
				break;
			
			if listA[aIndex] < listB[bIndex]:
				# put element from A into list
				sequence[sequenceIndex] = listA[aIndex]
				aIndex += 1
			else:
				# put element from B into list
				sequence[sequenceIndex] = listB[bIndex]
				bIndex += 1
			sequenceIndex += 1
