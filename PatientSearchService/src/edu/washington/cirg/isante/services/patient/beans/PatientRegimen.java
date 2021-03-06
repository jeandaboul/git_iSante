package edu.washington.cirg.isante.services.patient.beans;

import javax.xml.bind.annotation.XmlAttribute;



public class PatientRegimen {
	private String regimen;
	private String day;
	private String month;
	private String year;
	
	@XmlAttribute
	public String getRegimen() {
		return regimen;
	}
	public void setRegimen(String regimen) {
		this.regimen = regimen;
	}
	
	@XmlAttribute
	public String getDay() {
		return day;
	}
	public void setDay(String day) {
		this.day = day;
	}
	
	@XmlAttribute
	public String getMonth() {
		return month;
	}
	public void setMonth(String month) {
		this.month = month;
	}
	
	@XmlAttribute
	public String getYear() {
		return year;
	}
	
	
	public void setYear(String year) {
		this.year = year;
	}
	
}
