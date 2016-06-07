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

public class Job3 extends Configured implements Tool {

  private static final Logger LOG = Logger.getLogger(Job3.class);

  public static void main(String[] args) throws Exception {
    int res = ToolRunner.run(new Job3(), args);
    System.exit(res);
  }

  public int run(String[] args) throws Exception {//Configuracion, argumentos y clases por utilizar
    Job job = Job.getInstance(getConf(), "Job3");
    job.setJarByClass(this.getClass());
    
    FileInputFormat.addInputPath(job, new Path(args[0]));
    FileOutputFormat.setOutputPath(job, new Path(args[1]));
    job.setMapperClass(Map.class);
    job.setReducerClass(Reduce.class);
    job.setOutputKeyClass(Text.class);
    job.setOutputValueClass(IntWritable.class);
    return job.waitForCompletion(true) ? 0 : 1;
  }

  public static class Map extends Mapper<LongWritable, Text, Text, IntWritable> {
    private final static IntWritable one = new IntWritable(1);
    private Text word = new Text();
    private long numRecords = 0;    
    private static final Pattern WORD_BOUNDARY = Pattern.compile("\\s*\\b\\s*");

    public void map(LongWritable offset, Text lineText, Context context)
        throws IOException, InterruptedException {
      String line = lineText.toString();//lee la linea
      Text currentWord = new Text();
      for (String word : WORD_BOUNDARY.split(line)) {//Recorre los datos
        if (word.isEmpty()) {
            continue;
        }
            currentWord = new Text(word + " ");//Llave (Palabra
            context.write(currentWord,one);//Palabra y valor = 1
        }
    }
  }

  public static class Reduce extends Reducer<Text, IntWritable, Text, IntWritable> {
    @Override
    public void reduce(Text word, Iterable<IntWritable> counts, Context context)
        throws IOException, InterruptedException {
      int sum = 0;
      for (IntWritable count : counts) {//Suma de los valores por llave
        sum += count.get();
      }
      context.write(word, new IntWritable(sum));//Write de la palabra y el conteo
    }
  }
}
