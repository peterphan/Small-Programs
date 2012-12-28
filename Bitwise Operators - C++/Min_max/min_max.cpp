/* Computes min(x,y) and max(x,y) using bitwise
 * Source: http://graphics.stanford.edu/~seander/bithacks.html#IntegerMinOrMax
 * 
 * More just learning how it works by trying to explain it to myself
 * - Peter Phan
 */
 #include <iostream>
using namespace std;

int main() {
	int a,b;
	cout << "Enter first number: ";
	cin >> a;
	cout << "Enter second number: ";
	cin >> b;

	int smallerOfTwo = b ^ ((a ^ b) & -(a < b));
	/* If (a < b) is true, -(a < b) is all 1s.
	 * so ((a ^ b) & -1) = a^b
	 * Then b ^ a ^ b = a.
	 * 
	 * If (a <b) is false, then -(a < b) is all 0s.
	 * so ((a^b) & 0s) = 0
	 * then b ^ 0 = b
	 */

	int largerOfTwo = a ^ ((a ^ b) & -(a < b));
	/* If (a < b) is true, -(a < b) is all 1s.
	 * (a^b) & 1s = a^b
	 * a ^ a ^ b = b
	 * 
	 * otherwise, same result except with the result being a
	 */
	cout << "Smaller of the two was " << smallerOfTwo << endl;
	cout << "Larger of the two was " << largerOfTwo << endl;
	return 0;
}