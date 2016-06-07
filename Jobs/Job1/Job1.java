package org.myorg;
import java.io.IOException;
import java.util.regex.Pattern;
import org.apache.hadoop.conf.Configured;
import org.apache.hadoop.util.Tool;
import org.apache.hadoop.util.ToolRunner;
import org.apache.hadoop.mapreduce.Job;
import org.apache.hadoop.mapreduce.Mapper;
import org.apache.hadoop.mapreduce.Reducer;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.mapreduce.lib.input.FileInputFormat;
import org.apache.hadoop.mapreduce.lib.output.FileOutputFormat;
import org.apache.hadoop.io.IntWritable;
import org.apache.hadoop.io.ArrayWritable;
import org.apache.hadoop.io.LongWritable;
import org.apache.hadoop.io.Text;

import org.apache.log4j.Logger;

public class Job1 extends Configured implements Tool {

  private static final Logger LOG = Logger.getLogger(Job1.class);

  public static void main(String[] args) throws Exception {
    int res = ToolRunner.run(new Job1(), args);
    System.exit(res);
  }

  public int run(String[] args) throws Exception {//Configuracion, argumentos y clases por utilizar
    Job job = Job.getInstance(getConf(), "Job1");
    job.setJarByClass(this.getClass());

    FileInputFormat.addInputPath(job, new Path(args[0]));
    FileOutputFormat.setOutputPath(job, new Path(args[1]));
    job.setMapperClass(Map.class);
    job.setReducerClass(Reduce.class);
    job.setOutputKeyClass(Text.class);
    job.setOutputValueClass(Text.class);
    return job.waitForCompletion(true) ? 0 : 1;
  }


    public static class Map extends Mapper<LongWritable, Text, Text, Text> {
	    private final static IntWritable one = new IntWritable(1);
	    private Text word = new Text();
	    private long numRecords = 0;    
	    private static final Pattern WORD_BOUNDARY = Pattern.compile("\\s*\\b\\s*");
		public static String url = ""; // /-/
		public static String tag=""; // /*/
		public static String titulo="";// /Â/


    public void map(LongWritable offset, Text lineText, Context context)
        throws IOException, InterruptedException {
      String line = lineText.toString();//Lee la linea

      Text currentWord = new Text();

	for (String word : WORD_BOUNDARY.split(line)) {
        if (word.isEmpty()) {
            continue;
        }
	
	if(line.charAt(1) == '-'){ //url
		url = line.substring(3);
	}else 	if(line.charAt(1) == '*'){//tag
		tag = line.substring(3);
	}else 	if(line.charAt(1) == 'Â'){//titulo
		titulo = line.substring(4);
	}else{
            currentWord = new Text(word + " ");//Lave (palabra)
            context.write(currentWord,new Text(url));//Llave palabra con valor url
        }
	}
    }
  }
  public static class Reduce extends Reducer<Text, Text, Text, Text> {
    @Override
    public void reduce(Text word, Iterable<Text> counts, Context context)
        throws IOException, InterruptedException {
    	StringBuilder links = new StringBuilder();
	
      for (Text count : counts) {//Recorre los resultados
    	  links.append(count + " ");
        
      }
      context.write(word,new Text(links.toString()));//Palabra con los url
    }
  }
}
