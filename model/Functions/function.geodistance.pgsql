#Mysql commented version, now we have to translate
#CREATE FUNCTION `geodistance`(`sin_lat1` FLOAT,
#						  `cos_cos1` FLOAT, `cos_sin1` FLOAT,
#						  `sin_lat2` FLOAT,
#						  `cos_cos2` FLOAT, `cos_sin2` FLOAT)
#RETURNS float
#LANGUAGE SQL
#DETERMINISTIC
#CONTAINS SQL
#SQL SECURITY INVOKER
#BEGIN
#RETURN acos(sin_lat1*sin_lat2 + cos_cos1*cos_cos2 + cos_sin1*cos_sin2);
#END
