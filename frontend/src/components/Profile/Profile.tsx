import { useEffect, useRef, useState } from 'react'
import wsApi from '../../utils/websocketApi'
import Input from '../Input/Input'
// @ts-ignore
import classes from './Profile.module.css'

function Profile(props: any) {
	const [avatar, setAvatar] = useState<string | null>(null)
	const [username, setUsername] = useState<string>(props.username)
	const [selectedFile, setSelectedFile] = useState<File | null>(null)
	const [hideEmail, setHideEmail] = useState<boolean>(false)
	const [submit, goSubmit] = useState<number>(0)

	const isSubmitting = useRef(false)

	const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
		const file = event.target.files?.[0]
		if (file) {
			const reader = new FileReader()
			reader.onload = e => {
				setAvatar(e.target?.result as string)
			}
			reader.readAsDataURL(file)
			setSelectedFile(file)
		}
	}

	useEffect(() => {
		wsApi.getProfile()
		wsApi.on('getProfile', data => {
			console.log(data)
			if (data[0].avatar) {
				setAvatar(`http://localhost/uploads/avatars/${data[0].avatar}`)
			}
			setUsername(data[0].username)
			setHideEmail(!data[0].isEmailVisible)
		})
	}, [])

	useEffect(() => {
		if (!selectedFile && !username && !hideEmail) return
		if (isSubmitting.current) return

		isSubmitting.current = true

		let avatar: any
		if (selectedFile) {
			const reader = new FileReader()
			reader.readAsArrayBuffer(selectedFile)
			reader.onload = () => {
				const metadata = JSON.stringify({
					fileName: selectedFile.name,
					fileType: selectedFile.type,
				})
				const buffer = new Uint8Array(reader.result as ArrayBuffer)

				// Конвертируем Uint8Array в строку
				let binary = ''
				for (let i = 0; i < buffer.length; i++) {
					binary += String.fromCharCode(buffer[i])
				}
				const base64File = btoa(binary)

				avatar = {
					metadata: metadata,
					file: base64File, // Отправляем файл как base64 строку
				}

				wsApi.setProfile(avatar, username, hideEmail)
				props.closeFunction(false)
				isSubmitting.current = false
			}
		} else {
			wsApi.setProfile(null, username, hideEmail)
			props.closeFunction(false)
			isSubmitting.current = false
		}
	}, [submit])

	return (
		<div className={classes.Profile}>
			<form
				onSubmit={e => {
					e.preventDefault()
					goSubmit(submit + 1)
				}}
			>
				<div className={classes.avatarUploader}>
					<label htmlFor='avatarInput' className={classes.avatarLabel}>
						<div
							style={{
								backgroundImage: `url(${avatar})`,
							}}
							className={classes.avatar}
						/>
					</label>
					<input
						type='file'
						id='avatarInput'
						accept='image/*'
						className={classes.avatarInput}
						onChange={handleFileChange}
					/>
				</div>
				<input
					maxLength={20}
					className={classes.UsernameInput}
					type='text'
					value={username}
					onChange={e => setUsername(e.target.value)}
					onBlur={() => {
						if (username === '') {
							setUsername(props.username)
						}
					}}
				/>
				<Input
					type='checkbox'
					title='Скрыть почту'
					name='hideMail'
					checked={hideEmail}
					onChange={e => setHideEmail(e.target.checked)}
				/>
				<div className={classes.buttons}>
					<button
						onClick={() => {
							props.closeFunction(false)
						}}
						className={classes.cancel}
					>
						Отменить
					</button>
					<Input type='submit' title='Подтвердить' name='submit' />
				</div>
			</form>
		</div>
	)
}

export default Profile
