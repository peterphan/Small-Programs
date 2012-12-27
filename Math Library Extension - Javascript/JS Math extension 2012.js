/* 2012 Peter Phan
 * ****************************************************
 * Extension of Javascript's Math Random library
 *
 * Maximum value possible:
 * 		MAX_INT_VALUE = 2^32-1
 * Minimum value possible:
 * 		MIN_INT_VALUE = -MAX_INT_VALUE
 * Functions available added:
 * 	- MathExt.randomInt() : return N s.t. 0 <= N < MAX_INT_VALUE
 *	- MathExt.randomInt(a) : return N s.t. 0 <= N < A 
 *	- MathExt.randomInt(a,b) : return N s.t. a <= N < b
 *	- MathExt.randomFloat() : ""
 *	- MathExt.randomFloat(a) : ""
 *	- MathExt.randomFloat(a, b) : ""
 *	- MathExt.randomBoolean() : return 50/50 chance of true/false
 * 	- MathExt.randomBoolean(bias) : returns a bias result of true/false
 */

// The actual maximum/minimum values are larger than this
var MAX_INT_VALUE = 4294967295;
var MIN_INT_VALUE = -4294967295;

var generateRandomValue = function(numArgs, min, max) {
	switch (numArgs) {
		case 0:
			return Math.random()*MAX_INT_VALUE;
		case 1:
			if(min > MAX_INT_VALUE) min = MAX_INT_VALUE;
			return Math.random()*min;
		default:
			if(min < MIN_INT_VALUE) min = MIN_INT_VALUE;
			if(max > MAX_INT_VALUE) max = MAX_INT_VALUE;
			return Math.random()*(max-min) + min;
	}
};

Math.randomInt = function(min, max) {
	return Math.floor(generateRandomValue(arguments.length, min, max));
};

Math.randomFloat = function(min, max) {
	return generateRandomValue(arguments.length, min, max);
};

/* Bias of 0.5 gives 50% chance of returning true
 * Bias of 0.7 gives 70% chance of returning true
 */
Math.randomBoolean = function(bias) {
	if(arguments.length == 1) {
		var chance = Math.random();
		return bias >= chance;
	} else {
		return Math.random() >= 0.5;
	}
};
