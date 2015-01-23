import java.io.IOException;
import java.util.List;
import java.util.ArrayList;
import java.util.Map;
import java.util.HashMap;
import java.util.Random;

public class SecretSantaGenerator {
	static final String CURRENT_DIRECTORY = "src";
	static final String OUTPUT_DIRECTORY = "output";
	static final String PARTICIPANTS_FILE_PATH = "src/participants.txt";
	List<Pair> _participants;
	static final String EXTENSION_DELIMINATOR = ".";
	static final String DIRECTORY_SEPARATOR = "/";
	static final String ZIP_EXTENSION = "zip";
	static final String TEXT_EXTENSION = "txt";
	
	public void setParticipants(List<Pair> participants) {
		_participants = participants;
	}
	
	public Map<Pair, Pair> generatePairings() {
		Map<Pair, Pair> pairings = new HashMap<Pair, Pair>();
		Random random = new Random();
		random.setSeed(System.currentTimeMillis()); // set random seed
		
		List<Pair> toChooseFromList = new ArrayList<Pair>();
		for (Pair p : _participants) {
			toChooseFromList.add(p);
		}
		
		Pair []participants = toChooseFromList.toArray(new Pair[_participants.size()]);
		shuffle(participants);
		
		for (Pair p : participants) {
			while (!toChooseFromList.isEmpty()) {
				int index = random.nextInt(toChooseFromList.size());
				Pair partner = toChooseFromList.get(index);
				if (!p.equals(partner)) {
					pairings.put(p, partner);
					toChooseFromList.remove(index);
					break;
				}
			}
		}
		
		return pairings;
	}
	
	private void shuffle(Pair []participants) {
		Random random = new Random();
		random.setSeed(System.currentTimeMillis()); // set random seed
		
		for (int i = participants.length-1; i >= 0; i--) {
			int index = random.nextInt(i + 1);
			if (i != index) {
				Pair temp = participants[i];
				participants[i] = participants[index];
				participants[index] = temp;
			}
		}
	}
	
	public static void main(String []args) {
		SecretSantaGenerator generator = new SecretSantaGenerator();

		List<Pair> participants = null;
		try {
			 participants = PairReader.read(PARTICIPANTS_FILE_PATH);
		} catch (IOException e) {
			e.printStackTrace();
		}
		
		generator.setParticipants(participants);
		Map<Pair, Pair> pairings = generator.generatePairings();
		for (Map.Entry<Pair, Pair> entry : pairings.entrySet()) {
			System.out.println("Writing zip for " + entry.getKey().getName());
			String zipPath = generateZipPath(entry.getKey().getName());
			WriteMgr.writeToZip(entry.getKey(),  entry.getValue(), zipPath, entry.getKey().getName() + EXTENSION_DELIMINATOR + TEXT_EXTENSION);
		}
		System.out.println("DONE!");
	}
	
	private static String generateZipPath(String name) {
		return System.getProperty("user.dir") +
				DIRECTORY_SEPARATOR +
				CURRENT_DIRECTORY +
				DIRECTORY_SEPARATOR +
				OUTPUT_DIRECTORY +
				DIRECTORY_SEPARATOR +
				name +
				EXTENSION_DELIMINATOR +
				ZIP_EXTENSION;
	}
}
