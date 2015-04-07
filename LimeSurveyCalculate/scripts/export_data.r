arguments <- commandArgs(TRUE)

lime_data <- read.csv(
    arguments[1],
    header=TRUE,
    fill=TRUE,
    sep=","
)

lime_mark <-subset(
                   lime_data,
                   type == "mark"
                   )

lime_mark_level <- levels(droplevels(lime_mark$key))

lime_summary <- data.frame(
                           user = levels(
                                         droplevels(lime_mark$user)
                                        )
                            )

for (i in lime_mark_level){
    temp_data <- subset(lime_mark,
                        key == i,
                        c("value", "user")
                        )

    names(temp_data) <- c(i, "user")

    lime_summary <- merge(
                          lime_summary,
                          temp_data,
                          by = 'user',
                          all = TRUE
                          )
}

write.csv(lime_summary,
          file = paste(
		               strsplit(arguments[1],
					            '.csv',
								fixed = TRUE
							    )[1],
						' - summary.csv',
						sep = ''
						),
          row.names = TRUE,
          quote = TRUE
         )

cat('done \n')
alarm()