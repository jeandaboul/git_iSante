
delete from `patientStatusLookup`;

INSERT INTO `patientStatusLookup` (`statusValue`, `statusDescEn`, `statusDescFr`, `ReportOrder`) VALUES
(0,'unknown','Inconu',0),
(7,'Recent Pre-ART','R�cents en Pr�-ARV',7),
(10,'Lost to follow up Pre-ART','Perdus de vue en Pr�-ARV',8),
(4,'Deceased on Pre-ART','D�c�d�s en Pr�-ARV',9),
(5,'Transferred on Pre-ART','Transf�r�s en Pr�-ARV',10),
(6,'Regular on ART','R�guliers sous ARV',1),
(8,'Missed appointments on ART','Rendez-vous rates sous ARV',3),
(9,'Lost to follow on ART','Perdus de vue sous ARV',2),
(1,'Deceased ART','D�c�d�s sous ARV',4),
(3,'Discontinued on ART','Arr�t�s sous ARV',5),
(2,'Transferred on ART','Transf�r�s sous ARV',6),
(11,'Actif on Pre-Art','Actifs en Pr�-ARV',11);
