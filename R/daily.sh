source /home/leoniedu/.bash_profile
rm /home/leoniedu/reps/CongressoAberto/data/camara/rollcalls/*.zip
rm /home/leoniedu/reps/CongressoAberto/data/camara/rollcalls/extracted/*
/home/leoniedu/run/bin/R --vanilla < ~/reps/CongressoAberto/R/daily.R
