
import java.io.IOException;
import java.util.regex.Pattern;
import java.io.BufferedReader;
import java.io.InputStreamReader;

import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.conf.Configured;
import org.apache.hadoop.util.Tool;
import org.apache.hadoop.util.ToolRunner;
import org.apache.hadoop.mapreduce.Job;
import org.apache.hadoop.mapreduce.Mapper;
import org.apache.hadoop.mapreduce.Reducer;
import org.apache.hadoop.fs.FSDataInputStream;
import org.apache.hadoop.fs.FileSystem;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.mapreduce.lib.input.FileInputFormat;
import org.apache.hadoop.mapreduce.lib.output.FileOutputFormat;
import org.apache.hadoop.io.IOUtils;
import org.apache.hadoop.io.IntWritable;
import org.apache.hadoop.io.ArrayWritable;
import org.apache.hadoop.io.LongWritable;
import org.apache.hadoop.io.Text;
import org.apache.log4j.Logger;
import java.sql.*;//Importamos la librería

public class DatosJob3 {


/**
 *
 * @author PhoeniXtreme
 */

 private Connection conexion;//Creamos una variable privada para conectarnos
    
    public Connection getConexion() {//Metodo get para la conexion
    return conexion;
}    
    
    public void setConexion(Connection conexion) {//Metodo set para la conexion
        this.conexion = conexion;
}    
    public DatosJob3 conectar() {     
        
        try {
            Class.forName("com.mysql.jdbc.Driver");
            String BaseDeDatos = "jdbc:mysql://localhost:3306/proyecto?user=root&password=cloudera";
        ;
            setConexion(DriverManager.getConnection(BaseDeDatos));
        
            if(getConexion() != null){
                System.out.println("Conexion Exitosa!");
            }else{
                System.out.println("Conexion Fallida!");                
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
        return this;
    }
     public boolean ejecutar(String sql) {
        try {
            Statement sentencia = getConexion().createStatement(ResultSet.TYPE_FORWARD_ONLY, ResultSet.CONCUR_READ_ONLY);
            sentencia.executeUpdate(sql);
            sentencia.close();
        } catch (SQLException e) {
            e.printStackTrace();
            return false;
        }        return true;
    } 
    public ResultSet consultar(String sql) {
        ResultSet resultado;
        try {
            Statement sentencia = getConexion().createStatement(ResultSet.TYPE_FORWARD_ONLY, ResultSet.CONCUR_READ_ONLY);
            resultado = sentencia.executeQuery(sql);

        } catch (SQLException e) {
            e.printStackTrace();
            return null;
                  
        }       return resultado;
    }


	 public static void main(String[] args) throws Exception {
		DatosJob3 r = new DatosJob3();
		r.conectar();
		  Configuration conf = new Configuration();
		  FileSystem hdfs = FileSystem.get(conf);
		  Path path = new Path("/user/cloudera/job3/output/part-r-00000");
		BufferedReader br = new BufferedReader(new InputStreamReader (hdfs.open(path)));
            String line;
		
            line=br.readLine();//Lee la linea y asigna las variables
		String[] partes = line.split(" ");
		
		String palabra = partes[0];
		String numero = partes[1].replace("	","");
		
		
		int conteo = Integer.parseInt(numero);
			//SQL del Insert
		String sql = "Insert into job3 (Palabra, Conteo) Values('" + palabra + "'," + conteo + ");";
		//System.out.println(sql);
		r.ejecutar(sql);//Ejecuta el sql
           while (line != null){
		try{
                line=br.readLine();
		partes = line.split(" ");
		//System.out.println(partes[0] + "L" + partes [1]);
		palabra = partes[0];
		numero = partes[1].replace("	","");
		
		
		conteo = Integer.parseInt(numero);
		sql = "Insert into job3 (Palabra, Conteo) Values('" + palabra + "'," + conteo + ");";
		//System.out.println(sql);
		r.ejecutar(sql);
		}catch (Exception e) {System.out.println(e);}

		 }
	}
}
   


