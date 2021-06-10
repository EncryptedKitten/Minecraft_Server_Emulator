import glob, sys, os, getpass

def main():
	if (len(sys.argv)) == 1:
		search = getpass.getpass(prompt='Search: ', stream=None)
		sys.argv.append(search)

	for x in glob.glob("**/*", recursive=True):
		if os.path.isfile(x):
			f = open(x)
			fcon_l = f.read().lower().replace("\r", "").split("\n")
			f.seek(0)
			fcon_u = f.read().replace("\r", "").split("\n")
			f.close()

			for i in range(len(fcon_l)):
				for arg in sys.argv[1:]:
					arg = arg.lower()
					if arg in fcon_l[i]:
						start = fcon_l[i].find(arg) - 20

						prefix = ""
						if (start <= 0):
							start = 0
						else:
							prefix = "..."

						end = fcon_l[i].find(arg) + 20

						suffix = ""
						if (end >= len(fcon_l[i])):
							end = len(fcon_l[i])
						else:
							suffix = "..."

						part = prefix + fcon_u[i][start:end] + suffix
						print(x + ": " + str(i + 1) + ": " + part)


if __name__ == "__main__":
	main()