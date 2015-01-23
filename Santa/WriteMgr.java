import java.io.File;
import java.io.FileOutputStream;
import java.util.zip.ZipEntry;
import java.util.zip.ZipOutputStream;
import java.util.*;
import javax.mail.*;
import javax.mail.internet.*;
import javax.activation.*;

public class WriteMgr {

	public static boolean sendEMail(String fromEmail, String toEmail, String host, String filePath) {
		// Get system properties
		Properties properties = System.getProperties();
		// Setup mail server
		properties.setProperty("mail.smtp.host", host);
		// Get the default Session object.
		Session session = Session.getDefaultInstance(properties);

		try{
			// Create a default MimeMessage object.
			MimeMessage message = new MimeMessage(session);

			// Set headers
			message.setFrom(new InternetAddress(fromEmail));
			message.addRecipient(Message.RecipientType.TO,new InternetAddress(toEmail));
			message.setSubject("Your secret santa");
			
			// Create the message part 
			BodyPart messageBodyPart = new MimeBodyPart();

			// Fill the message
			messageBodyPart.setText("This is message body");
	         
			// Create a multipar message
			Multipart multipart = new MimeMultipart();

			// Set text message part
			multipart.addBodyPart(messageBodyPart);

			// Part two is attachment
			messageBodyPart = new MimeBodyPart();
			DataSource source = new FileDataSource(filePath);
			messageBodyPart.setDataHandler(new DataHandler(source));
			messageBodyPart.setFileName(filePath);
			multipart.addBodyPart(messageBodyPart);
			
			// Send the complete message parts
			message.setContent(multipart );
			
			// Send message
			Transport.send(message);
			System.out.println("Sent message successfully....");
			return true;
		} catch (MessagingException mex) {
			mex.printStackTrace();
		}
		return false;
	}
	
	public static boolean writeToZip(Pair from, Pair to, String zipPath, String filename) {
		final StringBuilder sb = new StringBuilder();
		sb.append("Hi " + from.getName() + ". Your secret santa is " + to.getName() + "!!!\n");
		sb.append("Have fun ;)");
	  
		final File f = new File(zipPath);
		ZipOutputStream out;
		try {
			out = new ZipOutputStream(new FileOutputStream(f));
			ZipEntry e = new ZipEntry(filename);
			out.putNextEntry(e);
		
			byte[] data = sb.toString().getBytes();
			out.write(data, 0, data.length);
			out.closeEntry();
			
			out.close();
		} catch (Exception e) {
			e.printStackTrace();
			return false;
		}
		
		return true;
	}
}
