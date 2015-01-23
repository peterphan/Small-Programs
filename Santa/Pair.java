
public class Pair {
	String _name;
	String _email;
	
	public Pair(String name, String email) {
		_name = name;
		_email = email;
	}
	
	public String getName() {
		return _name;
	}
	
	public String getEmail() {
		return _email;
	}
	
	@Override
	public boolean equals(Object o) {
		Pair compare;
		if (o instanceof Pair) {
			compare = (Pair)o;
		} else {
			return false;
		}
		
		return _name.equals(compare.getName()) && _email.equals(compare.getEmail());
	}
	
	@Override
	public String toString() {
		return _name + ": " + _email;
	}
}
