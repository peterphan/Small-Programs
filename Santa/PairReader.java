import java.io.BufferedReader;
import java.io.FileReader;
import java.io.IOException;
import java.util.List;
import java.util.Map;
import java.util.ArrayList;
import java.util.HashMap;

public class PairReader {
	private static final String SEPARATOR = ",";
	public static final Integer NAME = 0;
	public static final Integer EMAIL = 1;
	
	public static List<Pair> read(String file) throws IOException {
		BufferedReader br = new BufferedReader(new FileReader(file));
		String line;
		List<Pair> list = new ArrayList<Pair>();
		while ((line = br.readLine()) != null) {
		   Map<Integer, String> map = createMapFromLine(line);
		   Pair pair = createPair(map);
		   list.add(pair);
		}
		br.close();
		
		return list;
	}
	
	private static Map<Integer, String> createMapFromLine(String line) {
		String []fields = line.split(SEPARATOR);
		Map<Integer, String> map = new HashMap<Integer, String>();
		
		map.put(NAME, normalizeField(fields[0]));
		map.put(EMAIL, normalizeField(fields[1]));
		
		return map;
	}
	
	private static String normalizeField(String field) {
		String result = field.trim();
		return result;
	}
	
	private static Pair createPair(Map<Integer, String> map) {
		return new Pair(map.get(NAME), map.get(EMAIL));
	}
}
